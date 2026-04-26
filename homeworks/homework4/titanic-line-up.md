Да, здесь лучше думать не как “набор файлов”, а как **маленький устанавливаемый модуль Bitrix24 коробки**.

Основная идея:

```text
/local/modules/otus.titanic
```

Это будет учебный модуль, который после установки:

1. создаёт инфоблоки-справочники;
2. создаёт ORM-таблицы;
3. импортирует `titanic.csv`;
4. даёт интерфейс для просмотра пассажиров, билетов, кают, выживаемости и зависимостей.

В Bitrix модуль обычно лежит в `/local/modules/<module_id>`, содержит `/install/index.php`, `/install/step.php`, `/install/unstep.php`, `/install/version.php`, а `DoInstall()` и `DoUninstall()` отвечают за установку и удаление. В официальном учебном материале Bitrix показана именно такая структура и вызовы `RegisterModule()` / `UnRegisterModule()` внутри установщика. ([1C-Битрикс][1])

---

# 1. Общая архитектура проекта

Я бы сделал модуль так:

```text
homeworks/homework4

├── install/
│   ├── index.php
│   ├── step.php
│   ├── unstep.php
│   ├── version.php
│   ├── data/
│   │   └── titanic.csv
│   └── components/
│       └── otus/
│           ├── titanic.dashboard/
│           ├── titanic.passengers/
│           └── titanic.passenger.detail/
├──index.php

/local/modules/Titanic/
│
│
├── lib/
│   ├── Config/
│   │   └── TitanicConfig.php
│   │
│   ├── Install/
│   │   ├── ModuleInstaller.php
│   │   ├── IblockInstaller.php
│   │   ├── TableInstaller.php
│   │   ├── CsvImporter.php
│   │   └── DemoDataInstaller.php
│   │
│   ├── Orm/
│   │   ├── PassengerTable.php
│   │   ├── TicketTable.php
│   │   ├── CabinTable.php
│   │   └── PassengerCabinTable.php
│   │
│   ├── Repository/
│   │   ├── PassengerRepository.php
│   │   ├── TicketRepository.php
│   │   ├── CabinRepository.php
│   │   └── IblockDictionaryRepository.php
│   │
│   ├── Service/
│   │   ├── TitanicCsvParser.php
│   │   ├── TicketNormalizer.php
│   │   ├── CabinNormalizer.php
│   │   └── TitanicAnalyticsService.php
│   │
│   └── Admin/
│       └── Menu.php
│
├── admin/
│   ├── titanic_dashboard.php
│   ├── titanic_passengers.php
│   └── titanic_import.php
│
├── lang/
│   └── ru/
│
└── include.php
```

Смысл такой:

```text
install/     → установка модуля
lib/Orm/     → ORM-модели таблиц
lib/Service/ → бизнес-логика
lib/Repository/ → выборки
components/  → интерфейс
admin/       → страницы в админке
```

---

# 2. Какие инфоблоки создать

Инфоблоки здесь лучше использовать как **справочники**.

Bitrix в ORM рассматривает каждый инфоблок как отдельный тип данных со своим набором свойств. Для работы с ORM у инфоблока нужно задать `API_CODE`; через него формируются уникальные классы сущности, а свойства инфоблока в ORM представлены не просто как скалярные значения, а как связи с мини-сущностями `VALUE` / `DESCRIPTION`. ([1C-Битрикс][2])

## Инфоблок 1

```text
titanic_classes
```

Название:

```text
Классы Titanic
```

Элементы:

| CODE     | NAME         |
| -------- | ------------ |
| `first`  | Первый класс |
| `second` | Второй класс |
| `third`  | Третий класс |

Зачем нужен:

```text
PassengerTable.PCLASS_ELEMENT_ID
→ элемент инфоблока titanic_classes
```

---

## Инфоблок 2

```text
titanic_ports
```

Название:

```text
Порты посадки Titanic
```

Элементы:

| CODE      | NAME        |
| --------- | ----------- |
| `S`       | Southampton |
| `C`       | Cherbourg   |
| `Q`       | Queenstown  |
| `unknown` | Неизвестно  |

Зачем нужен:

```text
PassengerTable.EMBARKED_ELEMENT_ID
→ элемент инфоблока titanic_ports
```

---

## Инфоблок 3

```text
titanic_cabin_decks
```

Название:

```text
Палубы Titanic
```

Элементы:

| CODE      | NAME              |
| --------- | ----------------- |
| `A`       | Палуба A          |
| `B`       | Палуба B          |
| `C`       | Палуба C          |
| `D`       | Палуба D          |
| `E`       | Палуба E          |
| `F`       | Палуба F          |
| `G`       | Палуба G          |
| `T`       | Палуба T          |
| `unknown` | Палуба неизвестна |

Зачем нужен:

```text
PassengerTable.CABIN_DECK_ELEMENT_ID
→ элемент инфоблока titanic_cabin_decks
```

---

# 3. Какие ORM-таблицы создать

В Bitrix ORM своя таблица описывается классом-наследником `DataManager`; такой класс должен переопределять `getTableName()` и `getMap()`, а стандартные методы `add`, `update`, `delete`, `getByPrimary`, `getList`, `query` уже доступны через `DataManager`. ([1C-Битрикс][3])

## Таблица 1 — пассажиры

```text
otus_titanic_passengers
```

ORM-класс:

```text
Otus\Titanic\Orm\PassengerTable
```

Поля:

| Поле                    | Тип              | Зачем                        |
| ----------------------- | ---------------- | ---------------------------- |
| `ID`                    | integer PK       | внутренний ID                |
| `PASSENGER_EXTERNAL_ID` | integer          | `PassengerId` из CSV         |
| `FULL_NAME`             | string           | имя пассажира                |
| `SEX`                   | string           | пол                          |
| `AGE`                   | float nullable   | возраст                      |
| `SIBSP`                 | integer          | супруги / братья / сёстры    |
| `PARCH`                 | integer          | родители / дети              |
| `FARE`                  | float / decimal  | стоимость билета             |
| `SURVIVED`              | integer          | 0 / 1                        |
| `TICKET_ID`             | integer          | связь с таблицей билетов     |
| `PCLASS_ELEMENT_ID`     | integer          | связь с инфоблоком классов   |
| `EMBARKED_ELEMENT_ID`   | integer nullable | связь с инфоблоком портов    |
| `CABIN_DECK_ELEMENT_ID` | integer nullable | связь с инфоблоком палуб     |
| `CABIN_RAW`             | string nullable  | исходная строка Cabin из CSV |

---

## Таблица 2 — билеты

```text
otus_titanic_tickets
```

ORM-класс:

```text
Otus\Titanic\Orm\TicketTable
```

Поля:

| Поле              | Тип             | Зачем                             |
| ----------------- | --------------- | --------------------------------- |
| `ID`              | integer PK      | внутренний ID                     |
| `TICKET_RAW`      | string          | билет как в CSV                   |
| `TICKET_PREFIX`   | string nullable | `PC`, `CA`, `A`, `NUMERIC`        |
| `TICKET_NUMBER`   | string nullable | номер билета                      |
| `PASSENGER_COUNT` | integer         | сколько пассажиров с этим билетом |
| `FARE_TOTAL`      | float nullable  | общая стоимость по билету         |

Здесь ты тренируешь связь:

```text
TicketTable
→ OneToMany
→ PassengerTable
```

Один билет может быть у нескольких пассажиров.

---

## Таблица 3 — каюты

```text
otus_titanic_cabins
```

ORM-класс:

```text
Otus\Titanic\Orm\CabinTable
```

Поля:

| Поле              | Тип        | Зачем                    |
| ----------------- | ---------- | ------------------------ |
| `ID`              | integer PK | внутренний ID            |
| `CABIN_CODE`      | string     | `C85`, `B57`, `C23`      |
| `DECK_CODE`       | string     | `A`, `B`, `C`, etc.      |
| `DECK_ELEMENT_ID` | integer    | связь с инфоблоком палуб |

---

## Таблица 4 — связь пассажиров и кают

```text
otus_titanic_passenger_cabin
```

ORM-класс:

```text
Otus\Titanic\Orm\PassengerCabinTable
```

Поля:

| Поле           | Тип        | Зачем    |
| -------------- | ---------- | -------- |
| `PASSENGER_ID` | integer PK | пассажир |
| `CABIN_ID`     | integer PK | каюта    |

Здесь ты тренируешь:

```text
PassengerTable
→ ManyToMany
→ CabinTable
```

В официальной документации Bitrix для `ManyToMany` показан именно такой смысл: если у книги может быть несколько авторов, а у автора несколько книг, создаётся отдельная таблица с двумя ID, а в ORM связь описывается через `ManyToMany` и `configureTableName()`. ([1C-Битрикс][4])

---

# 4. Где будут `Reference`, `OneToMany`, `ManyToMany`

## `Reference`

Это основная связь “у текущей записи есть ID другой записи”.

В Bitrix для связи “много к одному” используется поле `Reference`; связь строится через `Join::on('this.FIELD_ID', 'ref.ID')`, а тип JOIN можно настроить через `configureJoinType()`. ([1C-Битрикс][5])

У тебя `Reference` будет здесь:

```text
PassengerTable.TICKET_ID
→ TicketTable.ID
```

```text
PassengerTable.PCLASS_ELEMENT_ID
→ элемент инфоблока titanic_classes
```

```text
PassengerTable.EMBARKED_ELEMENT_ID
→ элемент инфоблока titanic_ports
```

```text
PassengerTable.CABIN_DECK_ELEMENT_ID
→ элемент инфоблока titanic_cabin_decks
```

```text
CabinTable.DECK_ELEMENT_ID
→ элемент инфоблока titanic_cabin_decks
```

---

## `OneToMany`

В Bitrix `OneToMany` описывается на стороне “один”, а третьим параметром указывается имя `Reference`-поля в связанной сущности. В документации пример: издатель имеет много книг, а связь идёт через Reference-поле `PUBLISHER` в книге. ([1C-Битрикс][5])

У тебя:

```text
TicketTable
→ PASSENGERS
→ много PassengerTable
```

Смысл:

```text
один билет → несколько пассажиров
```

Пример интерфейса:

```text
Билет: CA. 2343
Пассажиров: 7
Выжило: 0
Средний возраст: ...
Общая стоимость Fare: ...
```

---

## `ManyToMany`

У тебя:

```text
PassengerTable
↔ CabinTable
через otus_titanic_passenger_cabin
```

Смысл:

```text
один пассажир может иметь несколько кают
одна каюта может быть связана с несколькими пассажирами
```

Это особенно удобно для строк вроде:

```text
C23 C25 C27
B57 B59 B63 B66
```

---

# 5. Правильный порядок установки

Я бы не делал строго так:

```text
создать инфоблоки
заполнить инфоблоки
создать таблицы
заполнить таблицы
```

Лучше так:

```text
1. Проверить окружение
2. Зарегистрировать модуль
3. Создать инфоблоки
4. Создать ORM-таблицы
5. Импортировать справочники в инфоблоки
6. Импортировать билеты
7. Импортировать каюты
8. Импортировать пассажиров
9. Создать связи passenger ↔ cabin
10. Скопировать компоненты / публичные страницы
11. Показать сообщение “модуль установлен”
```

Почему так лучше:

```text
инфоблоки и таблицы сначала создаются как структура
а потом импорт уже заполняет все данные и связи
```

Если сначала заполнить инфоблоки, а таблиц ещё нет — не страшно. Но для единого установщика удобнее сначала создать всю структуру, потом одним импортом наполнить данные.

---

# 6. Установщик модуля

Главный файл:

```text
/local/modules/otus.titanic/install/index.php
```

В нём класс:

```php
class otus_titanic extends CModule
```

Внутри:

```text
DoInstall()
DoUninstall()
InstallFiles()
UnInstallFiles()
InstallDB()
UnInstallDB()
```

Примерная логика `DoInstall()`:

```text
1. Проверить bitrix_sessid
2. Проверить модуль iblock
3. Зарегистрировать модуль
4. Создать инфоблоки
5. Создать таблицы
6. Импортировать CSV
7. Скопировать компоненты
8. Добавить админские страницы
9. Показать step.php
```

Примерная логика `DoUninstall()`:

```text
1. Спросить: удалить данные или оставить?
2. Если удалить данные:
   - удалить таблицы
   - удалить созданные инфоблоки
   - удалить опции модуля
3. Удалить файлы компонентов
4. Снять регистрацию модуля
5. Показать unstep.php
```

---

# 7. Где хранить ID созданных инфоблоков

Не надо хардкодить:

```php
IBLOCK_ID = 16
```

В учебном проекте это частая ошибка.

Нужно создать конфиг по кодам:

```php
[
    'classes' => [
        'code' => 'titanic_classes',
        'api_code' => 'TitanicClasses',
    ],
    'ports' => [
        'code' => 'titanic_ports',
        'api_code' => 'TitanicPorts',
    ],
    'decks' => [
        'code' => 'titanic_cabin_decks',
        'api_code' => 'TitanicCabinDecks',
    ],
]
```

После создания инфоблока сохранить ID в опции модуля:

```text
otus.titanic / IBLOCK_CLASSES_ID
otus.titanic / IBLOCK_PORTS_ID
otus.titanic / IBLOCK_DECKS_ID
```

Но в коде всё равно лучше искать по `CODE`, а ID использовать как кэш.

---

# 8. Как должен идти импорт CSV

Создай сервис:

```text
Otus\Titanic\Service\TitanicCsvParser
```

Он читает `install/data/titanic.csv` и отдаёт строки.

Дальше сервис:

```text
Otus\Titanic\Install\DemoDataInstaller
```

Делает импорт.

## Шаг 1. Создать справочники

Из CSV и заранее известной логики создать:

```text
Классы:
1 → Первый класс
2 → Второй класс
3 → Третий класс

Порты:
S → Southampton
C → Cherbourg
Q → Queenstown
empty → Неизвестно

Палубы:
A, B, C, D, E, F, G, T, unknown
```

---

## Шаг 2. Импортировать билеты

Из каждой строки взять `Ticket`.

Нормализовать:

```text
PC 17599       → prefix PC, number 17599
CA. 2343       → prefix CA, number 2343
347082         → prefix NUMERIC, number 347082
STON/O2. 3101282 → prefix STONO2, number 3101282
```

Добавить уникальные билеты в:

```text
otus_titanic_tickets
```

---

## Шаг 3. Импортировать каюты

Из `Cabin`:

```text
C85
C23 C25 C27
B57 B59 B63 B66
```

Разбить по пробелу.

Создать уникальные каюты:

```text
C85
C23
C25
C27
B57
B59
B63
B66
```

Для каждой каюты определить палубу:

```text
C85 → C
B57 → B
```

---

## Шаг 4. Импортировать пассажиров

Для каждой строки CSV:

```text
PassengerId
Survived
Pclass
Name
Sex
Age
SibSp
Parch
Ticket
Fare
Cabin
Embarked
```

Найти:

```text
ticket_id
pclass_element_id
embarked_element_id
deck_element_id
```

И добавить запись в:

```text
otus_titanic_passengers
```

---

## Шаг 5. Создать связи пассажир ↔ каюта

Если у пассажира:

```text
Cabin = C23 C25 C27
```

То добавить в `otus_titanic_passenger_cabin`:

```text
passenger_id → cabin_id C23
passenger_id → cabin_id C25
passenger_id → cabin_id C27
```

---

# 9. Важный принцип: импорт должен быть повторяемым

Установщик не должен ломаться, если ты запускаешь его второй раз.

Поэтому везде нужны проверки:

```text
инфоблок уже есть? → не создавать заново
элемент справочника уже есть? → обновить или пропустить
таблица уже есть? → не создавать заново
билет уже есть? → взять ID
каюта уже есть? → взять ID
пассажир с PassengerId уже есть? → обновить или пропустить
```

Иначе при каждом тесте установки у тебя будут дубли.

---

# 10. Интерфейс после установки

После установки должна появиться страница:

```text
/titanic/
```

Или админская страница:

```text
/bitrix/admin/titanic_dashboard.php
```

Bitrix в документации по мастерам отдельно подчёркивает, что публичная часть решения должна быть максимально простой и не содержать тяжёлую логику сайта — лучше вызывать компоненты, а логику держать внутри компонентов/классов. ([1C-Битрикс][1])

Я бы сделал 3 компонента.

---

## Компонент 1

```text
otus:titanic.dashboard
```

Показывает:

```text
Всего пассажиров
Сколько выжило
Сколько погибло
Процент выживших
Выживаемость по классам
Выживаемость по полу
Выживаемость по портам
Средний Fare по классам
Топ билетов по количеству пассажиров
```

---

## Компонент 2

```text
otus:titanic.passengers
```

Список пассажиров.

Фильтры:

```text
Класс
Порт посадки
Палуба
Пол
Выжил / погиб
Fare от / до
Возраст от / до
```

Колонки:

```text
Имя
Пол
Возраст
Класс
Порт
Палуба
Билет
Fare
Выжил
```

---

## Компонент 3

```text
otus:titanic.passenger.detail
```

Карточка пассажира:

```text
ФИО
Класс
Билет
Все пассажиры с таким билетом
Каюта / каюты
Палуба
Порт посадки
Fare
Выжил / погиб
```

---

# 11. Где использовать `registerRuntimeField`

`registerRuntimeField()` нужен для временного поля прямо в конкретном запросе. В документации Bitrix он описан как нестатический метод Query, который добавляет поле во время выполнения запроса; если поле нужно всегда, его описывают статически в карте ORM. ([1C-Битрикс][6])

В твоём проекте его удобно использовать для аналитики.

Например:

```text
выживаемость по классам
средний Fare по портам
количество пассажиров по палубам
количество пассажиров на один билет
```

То есть:

```text
простые постоянные связи → getMap()
разовая аналитика → registerRuntimeField()
```

---

# 12. Что будет хорошей демонстрацией для ДЗ

Минимум для принятия ДЗ:

```text
1. Есть ORM-модель своей таблицы PassengerTable
2. Есть минимум 2 инфоблока
3. PassengerTable связан с элементами инфоблоков
4. Есть страница вывода списка
5. В списке видны данные из своей таблицы + данные из инфоблоков
```

Хороший расширенный вариант:

```text
1. PassengerTable
2. TicketTable
3. CabinTable
4. PassengerCabinTable
5. Reference: passenger → ticket
6. Reference: passenger → iblock elements
7. OneToMany: ticket → passengers
8. ManyToMany: passengers ↔ cabins
9. registerRuntimeField для статистики
10. кеширование аналитических запросов
```

---

# 13. План разработки по шагам

## Этап 1. Каркас модуля

Создать:

```text
/local/modules/otus.titanic/
```

Файлы:

```text
install/index.php
install/version.php
install/step.php
install/unstep.php
include.php
```

Цель этапа:

```text
модуль виден в списке модулей
его можно установить и удалить
```

---

## Этап 2. Конфиг модуля

Создать:

```text
lib/Config/TitanicConfig.php
```

Там хранить:

```text
module_id
коды инфоблоков
api_code инфоблоков
названия таблиц
пути к CSV
коды опций
```

Цель:

```text
не размазывать строки по всему проекту
```

---

## Этап 3. ORM-модели таблиц

Создать:

```text
lib/Orm/PassengerTable.php
lib/Orm/TicketTable.php
lib/Orm/CabinTable.php
lib/Orm/PassengerCabinTable.php
```

Цель:

```text
описать структуру БД через DataManager
```

---

## Этап 4. Создание таблиц при установке

Создать:

```text
lib/Install/TableInstaller.php
```

Он должен:

```text
проверить существование таблиц
создать таблицы
удалить таблицы при uninstall, если выбрано удаление данных
```

---

## Этап 5. Создание инфоблоков

Создать:

```text
lib/Install/IblockInstaller.php
```

Он должен:

```text
создать тип инфоблока otus_titanic
создать titanic_classes
создать titanic_ports
создать titanic_cabin_decks
задать API_CODE
сохранить ID в опции модуля
```

---

## Этап 6. CSV-парсер

Создать:

```text
lib/Service/TitanicCsvParser.php
```

Он должен:

```text
читать CSV
возвращать массив строк
нормально обрабатывать пустые Age, Cabin, Embarked
```

---

## Этап 7. Нормализаторы

Создать:

```text
lib/Service/TicketNormalizer.php
lib/Service/CabinNormalizer.php
```

`TicketNormalizer`:

```text
PC 17599 → PC / 17599
347082 → NUMERIC / 347082
```

`CabinNormalizer`:

```text
C23 C25 C27 → [C23, C25, C27]
C85 → deck C
empty → unknown
```

---

## Этап 8. Импорт данных

Создать:

```text
lib/Install/DemoDataInstaller.php
```

Он делает:

```text
1. импорт справочников в инфоблоки
2. импорт tickets
3. импорт cabins
4. импорт passengers
5. импорт passenger_cabin связей
```

---

## Этап 9. Репозитории

Создать:

```text
lib/Repository/PassengerRepository.php
lib/Repository/TicketRepository.php
lib/Repository/CabinRepository.php
```

Они нужны, чтобы компоненты не писали SQL/ORM-запросы прямо в `component.php`.

---

## Этап 10. Аналитический сервис

Создать:

```text
lib/Service/TitanicAnalyticsService.php
```

Методы:

```text
getTotalPassengers()
getSurvivalStats()
getSurvivalByClass()
getSurvivalBySex()
getSurvivalByPort()
getAverageFareByClass()
getTopSharedTickets()
getPassengersWithoutKnownCabin()
```

---

## Этап 11. Компоненты интерфейса

Создать:

```text
install/components/otus/titanic.dashboard/
install/components/otus/titanic.passengers/
install/components/otus/titanic.passenger.detail/
```

После установки они копируются в:

```text
/local/components/otus/
```

---

## Этап 12. Админское меню

Добавить пункт:

```text
Titanic ORM
```

Внутри:

```text
Дашборд
Пассажиры
Импорт
```

---

## Этап 13. Кеширование

Кешировать не сырые пассажирские списки, а аналитику:

```text
выживаемость по классам
выживаемость по полу
топ билетов
средний Fare
```

Кеш сбрасывать после повторного импорта.

---

# 14. Главная схема проекта

```text
CSV titanic.csv
      │
      ▼
TitanicCsvParser
      │
      ├── IblockInstaller / Dictionary import
      │       ├── titanic_classes
      │       ├── titanic_ports
      │       └── titanic_cabin_decks
      │
      ├── TicketTable
      │       └── OneToMany → PassengerTable
      │
      ├── CabinTable
      │       └── ManyToMany ↔ PassengerTable
      │
      └── PassengerTable
              ├── Reference → TicketTable
              ├── Reference → titanic_classes
              ├── Reference → titanic_ports
              └── Reference → titanic_cabin_decks
```

---

# 15. Как я бы назвал учебное приложение

```text
OTUS Titanic ORM
```

Или модульно:

```text
otus.titanic
```

Публичная страница:

```text
/titanic/
```

Админская страница:

```text
/bitrix/admin/otus_titanic_dashboard.php
```

---

# Короткий итог

Тебе нужен не просто скрипт импорта, а маленький учебный модуль:

```text
otus.titanic
```

Внутри него:

```text
инфоблоки = справочники
таблицы = основные данные и связи
сервисы = импорт и аналитика
репозитории = ORM-запросы
компоненты = интерфейс
установщик = создание всего проекта
```

Самая правильная учебная траектория:

```text
1. Сначала сделать модуль, который устанавливается.
2. Потом создать инфоблоки.
3. Потом создать ORM-таблицы.
4. Потом импортировать CSV.
5. Потом сделать список пассажиров.
6. Потом добавить аналитику.
7. Потом отдельно потренировать Reference / OneToMany / ManyToMany / registerRuntimeField.
```

[1]: https://dev.1c-bitrix.ru/learning/course/?COURSE_ID=101&TYPE=Y "Маркетплейс Bitrix Framework"
[2]: https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=12866 "Концепция и архитектура"
[3]: https://dev.1c-bitrix.ru/api_d7/bitrix/main/entity/datamanager/index.php "Главный модуль:DataManager"
[4]: https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=11741 "N:M"
[5]: https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=11737 "1:N "
[6]: https://dev.1c-bitrix.ru/api_d7/bitrix/main/entity/query/registerruntimefield.php "registerRuntimeField"
