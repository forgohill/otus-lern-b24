Коротко: **главная учебная точка — таблица `otus_titanic_passengers`**. От неё ты будешь пробовать связи с инфоблоками и другими таблицами.

В текущей идее с Titanic лучше сделать так:

```text
Пассажиры = своя таблица БД
Классы / порты / палубы = инфоблоки-справочники
Билеты / каюты = дополнительные таблицы для тренировки связей
```

---

# 1. Где пробовать `Reference`

`Reference` — это связь вида:

```text
одна запись хранит ID другой записи
```

В Bitrix D7 такие связи описываются через ORM relation fields, а `Join::on()` используется для условия соединения таблиц. В официальном курсе Bitrix пример связи 1:N строится именно через `Reference` и `Join::on`, с возможностью настроить тип JOIN. ([1C-Битрикс][1])

У тебя `Reference` можно пробовать здесь:

```text
otus_titanic_passengers.PCLASS_ELEMENT_ID
→ элемент инфоблока titanic_classes
```

```text
otus_titanic_passengers.EMBARKED_ELEMENT_ID
→ элемент инфоблока titanic_ports
```

```text
otus_titanic_passengers.CABIN_DECK_ELEMENT_ID
→ элемент инфоблока titanic_cabin_decks
```

То есть пассажир ссылается на элементы инфоблоков.

Пример смысла:

```text
Пассажир: Braund, Mr. Owen Harris
PCLASS_ELEMENT_ID = ID элемента "Третий класс"
EMBARKED_ELEMENT_ID = ID элемента "Southampton"
CABIN_DECK_ELEMENT_ID = ID элемента "Неизвестно"
```

Вот здесь ты тренируешь:

```text
своя таблица БД → инфоблок
```

---

# 2. Где пробовать `OneToMany`

`OneToMany` — это связь:

```text
одна запись → много записей
```

В твоём Titanic это лучше всего делать через **билеты**.

Почему?

Потому что один билет в CSV может быть у нескольких пассажиров.

Пример:

```text
Ticket: 347082
Пассажиров: 7
```

Значит, можно создать отдельную таблицу:

```text
otus_titanic_tickets
```

И в таблице пассажиров хранить:

```text
TICKET_ID
```

Связь будет такая:

```text
otus_titanic_tickets.ID
→ много пассажиров из otus_titanic_passengers
```

Схема:

```text
otus_titanic_tickets
│
└── один билет
    ├── пассажир 1
    ├── пассажир 2
    ├── пассажир 3
    └── пассажир 4
```

То есть:

```text
TicketTable
→ OneToMany
→ PassengerTable
```

А в обратную сторону у пассажира будет обычный `Reference`:

```text
PassengerTable.TICKET_ID
→ TicketTable.ID
```

Это самый понятный вариант для тренировки `OneToMany`.

---

# 3. Где пробовать `ManyToMany`

`ManyToMany` — это связь:

```text
много записей ↔ много записей
```

В Titanic самый естественный вариант — **пассажиры и каюты**.

Почему?

Потому что в `Cabin` иногда есть несколько кают:

```text
C23 C25 C27
B57 B59 B63 B66
```

И при этом одна каюта теоретически может относиться к нескольким пассажирам.

Тогда можно создать таблицу кают:

```text
otus_titanic_cabins
```

И промежуточную таблицу связи:

```text
otus_titanic_passenger_cabin
```

Схема:

```text
otus_titanic_passengers
│
├── passenger_id
│
otus_titanic_passenger_cabin
│
├── passenger_id
└── cabin_id
│
otus_titanic_cabins
```

Пример:

```text
Пассажир 100 → C23
Пассажир 100 → C25
Пассажир 100 → C27
```

Вот здесь ты тренируешь настоящую `ManyToMany`.

В официальном курсе Bitrix связь N:M разбирается как отдельный тип связи через промежуточную таблицу между двумя сущностями. ([1C-Битрикс][2])

---

# 4. Как связать таблицы с инфоблоками

Инфоблоки у тебя должны быть **справочниками**.

## Инфоблок 1

```text
titanic_classes
```

Элементы:

```text
Первый класс
Второй класс
Третий класс
```

Связь:

```text
otus_titanic_passengers.PCLASS_ELEMENT_ID
→ titanic_classes element ID
```

---

## Инфоблок 2

```text
titanic_ports
```

Элементы:

```text
Southampton
Cherbourg
Queenstown
```

Связь:

```text
otus_titanic_passengers.EMBARKED_ELEMENT_ID
→ titanic_ports element ID
```

---

## Инфоблок 3

```text
titanic_cabin_decks
```

Элементы:

```text
Палуба A
Палуба B
Палуба C
Палуба D
Палуба E
Палуба F
Палуба G
Палуба T
Неизвестно
```

Связь:

```text
otus_titanic_passengers.CABIN_DECK_ELEMENT_ID
→ titanic_cabin_decks element ID
```

---

# 5. Лучшая учебная схема для твоего ДЗ

Я бы сделал так:

```text
Инфоблоки:
1. titanic_classes
2. titanic_ports
3. titanic_cabin_decks

Таблицы БД:
1. otus_titanic_passengers
2. otus_titanic_tickets
3. otus_titanic_cabins
4. otus_titanic_passenger_cabin
```

Полная схема:

```text
                         titanic_classes
                              ▲
                              │ Reference
                              │
titanic_ports ◄── Reference ── otus_titanic_passengers ── Reference ──► titanic_cabin_decks
                              │
                              │ Reference
                              ▼
                    otus_titanic_tickets
                              ▲
                              │ OneToMany
                              │
                    много пассажиров на один билет


otus_titanic_passengers
        ▲
        │ ManyToMany через otus_titanic_passenger_cabin
        ▼
otus_titanic_cabins
```

---

# 6. Где конкретно что тренировать

| Что хочешь потренировать       | Где делать                                               |
| ------------------------------ | -------------------------------------------------------- |
| `Reference` таблица → инфоблок | `PassengerTable.PCLASS_ELEMENT_ID` → класс               |
| `Reference` таблица → инфоблок | `PassengerTable.EMBARKED_ELEMENT_ID` → порт              |
| `Reference` таблица → инфоблок | `PassengerTable.CABIN_DECK_ELEMENT_ID` → палуба          |
| `Reference` таблица → таблица  | `PassengerTable.TICKET_ID` → `TicketTable.ID`            |
| `OneToMany`                    | один билет → много пассажиров                            |
| `ManyToMany`                   | пассажиры ↔ каюты                                        |
| `registerRuntimeField`         | временно подключить инфоблок или таблицу прямо в запросе |
| выборка свойств инфоблоков     | вывести имя класса, порт посадки, палубу                 |

`registerRuntimeField` как раз полезен, когда не хочешь описывать связь постоянно в `getMap()`, а хочешь добавить поле только на время конкретного запроса; в документации Bitrix он описан как добавление поля во время выполнения запроса. ([1C-Битрикс][3])

---

# 7. Минимальный вариант для сдачи

Для самого ДЗ достаточно:

```text
1. otus_titanic_passengers

2. titanic_classes
3. titanic_ports
4. titanic_cabin_decks
```

И этого уже хватит, чтобы показать:

```text
своя таблица БД связана минимум с двумя инфоблоками
```

Но если ты хочешь именно нормально потрогать:

```text
Reference
OneToMany
ManyToMany
```

тогда добавь ещё:

```text
otus_titanic_tickets
otus_titanic_cabins
otus_titanic_passenger_cabin
```

---

# Мой совет

Делай в 2 этапа.

**Этап 1 — для сдачи ДЗ:**

```text
PassengerTable
+ titanic_classes
+ titanic_ports
+ titanic_cabin_decks
```

Тут ты тренируешь `Reference` и связь своей таблицы с инфоблоками.

**Этап 2 — для нормального изучения ORM-связей:**

```text
TicketTable
CabinTable
PassengerCabinTable
```

Тут ты тренируешь `OneToMany` и `ManyToMany`.

Так структура не будет слишком сложной сразу, но даст тебе все нужные типы связей.

[1]: https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=11737&utm_source=chatgpt.com "1:N"
[2]: https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=11741&utm_source=chatgpt.com "N:M"
[3]: https://dev.1c-bitrix.ru/api_d7/bitrix/main/entity/query/registerruntimefield.php?utm_source=chatgpt.com "registerRuntimeField"
