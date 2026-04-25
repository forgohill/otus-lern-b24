
Да, здесь лучше делать **не одну большую таблицу**, а учебную нормализованную схему.

Я посмотрел оба файла:

* `masterpieces.json` — 97 объектов. Главная сущность: музейный предмет/экспонат. Внутри есть `path`, `year`, `inv_num`, `type.ru`, `country.ru`, `period.name.ru`, `name.ru`, `material.ru`, `building`, `hall`, `gallery`, `authors`, `collectors` и другие поля. 
* `buildings.json` — 11 зданий. Там есть здания, расписание, этажи, залы, экспозиции, картинки, адреса и тексты. Например, здание `116` содержит `schedule`, `name.ru`, `menu.ru`, `brief.ru`, `floors`, `halls`. 

## Главная мысль

Главная таблица должна быть:

```text
museum_masterpieces
```

Это один экспонат из `masterpieces.json`.

Все остальное — справочники, связи и дополнительные таблицы:

```text
museum_buildings
museum_floors
museum_halls
museum_expositions
museum_authors / museum_persons
museum_masterpiece_persons
museum_masterpiece_images
museum_types
museum_countries
museum_periods
```

То есть структура примерно такая:

```text
Здание
 └── Этаж
      └── Зал
           └── Экспонаты

Экспонат
 ├── тип
 ├── страна
 ├── период
 ├── авторы
 ├── коллекционеры
 └── изображения
```

---

# 1. Основные таблицы

## 1.1. `museum_masterpieces`

Главная таблица. Один ряд = один экспонат.

```sql
CREATE TABLE museum_masterpieces (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    source_id INT UNSIGNED NOT NULL,
    source_parent_id INT UNSIGNED NULL,

    building_id INT UNSIGNED NULL,
    hall_id INT UNSIGNED NULL,

    type_id INT UNSIGNED NULL,
    country_id INT UNSIGNED NULL,
    period_id INT UNSIGNED NULL,
    paint_school_id INT UNSIGNED NULL,
    graphics_type_id INT UNSIGNED NULL,

    department_source_id INT UNSIGNED NULL,

    path VARCHAR(500) NULL,
    inventory_number VARCHAR(100) NULL,

    creation_year INT NULL,
    acquisition_year VARCHAR(20) NULL,

    name_ru VARCHAR(500) NOT NULL,
    name_comment_ru VARCHAR(500) NULL,

    period_text_ru VARCHAR(500) NULL,
    size_ru VARCHAR(500) NULL,
    material_ru TEXT NULL,

    description_ru MEDIUMTEXT NULL,
    annotation_ru MEDIUMTEXT NULL,

    origin_ru TEXT NULL,
    link_url VARCHAR(500) NULL,
    link_text_ru VARCHAR(500) NULL,

    search_text_ru MEDIUMTEXT NULL,
    search_keywords_ru TEXT NULL,

    is_masterpiece TINYINT(1) NOT NULL DEFAULT 0,
    show_in_hall TINYINT(1) NOT NULL DEFAULT 0,
    show_in_collection TINYINT(1) NOT NULL DEFAULT 0,
    is_cast TINYINT(1) NOT NULL DEFAULT 0,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY ux_museum_masterpieces_source_id (source_id),
    KEY ix_museum_masterpieces_building_id (building_id),
    KEY ix_museum_masterpieces_hall_id (hall_id),
    KEY ix_museum_masterpieces_type_id (type_id),
    KEY ix_museum_masterpieces_country_id (country_id),
    KEY ix_museum_masterpieces_period_id (period_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Почему так:

* `source_id` — это ключ из JSON, например `3687`, `3675`, `4005`.
* `id` — внутренний ID в нашей БД.
* `building_id`, `hall_id` — связь с таблицами зданий и залов.
* `type_id`, `country_id`, `period_id` — связи со справочниками.
* `name_ru`, `description_ru`, `material_ru` — только русский интерфейс.
* `description_ru` — это поле `text.ru`.
* `annotation_ru` — это поле `annotation.ru`.

---

# 2. Справочники

## 2.1. Типы экспонатов

Из поля:

```json
"type": {
  "ru": "Живопись"
}
```

```sql
CREATE TABLE museum_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name_ru VARCHAR(255) NOT NULL,

    UNIQUE KEY ux_museum_types_name_ru (name_ru)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Примеры типов из файла:

```text
Живопись
Графика
Скульптура
Археология
Прикладное искусство
Нумизматика
Слепки
```

---

## 2.2. Страны

Из поля:

```json
"country": {
  "ru": "Франция"
}
```

```sql
CREATE TABLE museum_countries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name_ru VARCHAR(255) NOT NULL,

    UNIQUE KEY ux_museum_countries_name_ru (name_ru)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 2.3. Периоды

В JSON период состоит из двух частей:

```json
"period": {
  "name": {
    "ru": "XIX век"
  },
  "text": {
    "ru": "Около 1888"
  }
}
```

`period.name.ru` лучше вынести в справочник, а `period.text.ru` оставить в самом экспонате, потому что это уточнение конкретного предмета.

```sql
CREATE TABLE museum_periods (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name_ru VARCHAR(255) NOT NULL,

    UNIQUE KEY ux_museum_periods_name_ru (name_ru)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 2.4. Художественная школа

Из поля:

```json
"paint_school": {
  "ru": "Франция"
}
```

```sql
CREATE TABLE museum_paint_schools (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name_ru VARCHAR(255) NOT NULL,

    UNIQUE KEY ux_museum_paint_schools_name_ru (name_ru)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 2.5. Тип графики

Из поля:

```json
"graphics_type": {
  "ru": "Оригинальная графика"
}
```

```sql
CREATE TABLE museum_graphics_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name_ru VARCHAR(255) NOT NULL,

    UNIQUE KEY ux_museum_graphics_types_name_ru (name_ru)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 3. Здания, этажи, залы

## 3.1. `museum_buildings`

```sql
CREATE TABLE museum_buildings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    source_id INT UNSIGNED NOT NULL,
    sort INT NULL,

    path VARCHAR(500) NULL,
    is_closed TINYINT(1) NOT NULL DEFAULT 0,

    ticket_url VARCHAR(500) NULL,
    picture_path VARCHAR(500) NULL,

    map_zoom INT NULL,
    latitude DECIMAL(10, 7) NULL,
    longitude DECIMAL(10, 7) NULL,

    app_number VARCHAR(50) NULL,
    app_number_tablet VARCHAR(50) NULL,

    name_ru VARCHAR(500) NOT NULL,
    menu_ru VARCHAR(500) NULL,
    brief_ru TEXT NULL,

    text_ru MEDIUMTEXT NULL,
    text_more_ru MEDIUMTEXT NULL,

    address_ru TEXT NULL,
    timeline_ru TEXT NULL,
    rate_ru TEXT NULL,
    tel_ru TEXT NULL,
    excursions_ru TEXT NULL,
    accessibility_ru TEXT NULL,
    rules_ru TEXT NULL,
    audioguide_ru TEXT NULL,

    search_text_ru MEDIUMTEXT NULL,
    search_keywords_ru TEXT NULL,

    panorama_url VARCHAR(500) NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY ux_museum_buildings_source_id (source_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Поле `yamapcoords` в JSON выглядит так:

```text
17,55.747272,37.605283
```

Его лучше разобрать на:

```text
map_zoom = 17
latitude = 55.747272
longitude = 37.605283
```

---

## 3.2. `museum_floors`

```sql
CREATE TABLE museum_floors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    source_id INT UNSIGNED NOT NULL,
    building_id INT UNSIGNED NOT NULL,

    path VARCHAR(500) NULL,
    number VARCHAR(50) NULL,
    name_ru VARCHAR(255) NOT NULL,
    plan_path VARCHAR(500) NULL,

    UNIQUE KEY ux_museum_floors_source_id (source_id),
    KEY ix_museum_floors_building_id (building_id),

    CONSTRAINT fk_museum_floors_building
        FOREIGN KEY (building_id)
        REFERENCES museum_buildings(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 3.3. `museum_halls`

```sql
CREATE TABLE museum_halls (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    source_id INT UNSIGNED NOT NULL,
    building_id INT UNSIGNED NOT NULL,
    floor_id INT UNSIGNED NULL,

    path VARCHAR(500) NULL,
    number VARCHAR(50) NULL,

    name_ru VARCHAR(500) NOT NULL,
    short_ru MEDIUMTEXT NULL,
    text_ru MEDIUMTEXT NULL,

    img_path VARCHAR(500) NULL,

    search_text_ru MEDIUMTEXT NULL,
    search_keywords_ru TEXT NULL,

    virtual_tour_start_ru VARCHAR(500) NULL,
    virtual_tour_preview_pc VARCHAR(500) NULL,
    virtual_tour_preview_mob VARCHAR(500) NULL,

    UNIQUE KEY ux_museum_halls_source_id (source_id),
    KEY ix_museum_halls_building_id (building_id),
    KEY ix_museum_halls_floor_id (floor_id),

    CONSTRAINT fk_museum_halls_building
        FOREIGN KEY (building_id)
        REFERENCES museum_buildings(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_museum_halls_floor
        FOREIGN KEY (floor_id)
        REFERENCES museum_floors(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 4. Экспозиции

В `buildings.json` есть отдельный блок `exposition`. Также у залов есть список экспозиций.

Значит нужна таблица экспозиций и связующая таблица залов с экспозициями.

## 4.1. `museum_expositions`

```sql
CREATE TABLE museum_expositions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    source_id INT UNSIGNED NOT NULL,
    building_id INT UNSIGNED NULL,

    path VARCHAR(500) NULL,
    img_path VARCHAR(500) NULL,
    circ_img_path VARCHAR(500) NULL,

    name_ru VARCHAR(500) NOT NULL,
    short_ru MEDIUMTEXT NULL,
    text_ru MEDIUMTEXT NULL,

    halls_list_header_ru TEXT NULL,

    search_text_ru MEDIUMTEXT NULL,
    search_keywords_ru TEXT NULL,

    UNIQUE KEY ux_museum_expositions_source_id (source_id),
    KEY ix_museum_expositions_building_id (building_id),

    CONSTRAINT fk_museum_expositions_building
        FOREIGN KEY (building_id)
        REFERENCES museum_buildings(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 4.2. `museum_exposition_halls`

Многие-ко-многим:

```sql
CREATE TABLE museum_exposition_halls (
    exposition_id INT UNSIGNED NOT NULL,
    hall_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (exposition_id, hall_id),

    CONSTRAINT fk_museum_exposition_halls_exposition
        FOREIGN KEY (exposition_id)
        REFERENCES museum_expositions(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_museum_exposition_halls_hall
        FOREIGN KEY (hall_id)
        REFERENCES museum_halls(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 5. Авторы, коллекционеры, участники

В `masterpieces.json` поля `authors` и `collectors` иногда пустая строка, а иногда объект:

```json
"authors": {
  "1": {
    "ru": "Поль Сезанн",
    "comment": {
      "ru": "автор"
    }
  }
}
```

Это классическая связь **многие ко многим**:

```text
один экспонат может иметь несколько авторов
один автор может быть связан с несколькими экспонатами
```

## 5.1. `museum_persons`

```sql
CREATE TABLE museum_persons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name_ru VARCHAR(500) NOT NULL,

    UNIQUE KEY ux_museum_persons_name_ru (name_ru)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 5.2. `museum_masterpiece_persons`

```sql
CREATE TABLE museum_masterpiece_persons (
    masterpiece_id INT UNSIGNED NOT NULL,
    person_id INT UNSIGNED NOT NULL,

    role VARCHAR(50) NOT NULL,
    role_comment_ru VARCHAR(255) NULL,
    sort INT UNSIGNED NOT NULL DEFAULT 500,

    PRIMARY KEY (masterpiece_id, person_id, role),

    KEY ix_museum_masterpiece_persons_person_id (person_id),
    KEY ix_museum_masterpiece_persons_role (role),

    CONSTRAINT fk_museum_masterpiece_persons_masterpiece
        FOREIGN KEY (masterpiece_id)
        REFERENCES museum_masterpieces(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_museum_masterpiece_persons_person
        FOREIGN KEY (person_id)
        REFERENCES museum_persons(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Значения `role`:

```text
author
collector
maker
```

---

# 6. Изображения экспонатов

В `gallery` у экспонатов структура такая:

```json
"gallery": {
  "1": {
    "id01": "/path/image_01.jpg",
    "id02": "/path/image_02.jpg",
    "id03": "/path/image_03.jpg"
  }
}
```

Это не надо хранить JSON-строкой в главной таблице. Лучше отдельная таблица.

```sql
CREATE TABLE museum_masterpiece_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    masterpiece_id INT UNSIGNED NOT NULL,

    gallery_group INT UNSIGNED NOT NULL,
    image_code VARCHAR(20) NOT NULL,
    sort INT UNSIGNED NOT NULL DEFAULT 500,

    path VARCHAR(500) NOT NULL,

    KEY ix_museum_masterpiece_images_masterpiece_id (masterpiece_id),

    CONSTRAINT fk_museum_masterpiece_images_masterpiece
        FOREIGN KEY (masterpiece_id)
        REFERENCES museum_masterpieces(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Пример:

```text
gallery_group = 1
image_code = id01
path = /data/fonds/ancient_east/...
```

---

# 7. Расписание зданий

В `buildings.json` у зданий есть регулярное расписание и исключения.

## 7.1. Регулярное расписание

```sql
CREATE TABLE museum_building_schedule_regular (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    building_id INT UNSIGNED NOT NULL,

    weekday_code VARCHAR(10) NOT NULL,
    time_begin TIME NULL,
    time_end TIME NULL,
    is_closed TINYINT(1) NOT NULL DEFAULT 0,

    UNIQUE KEY ux_museum_building_schedule_regular (building_id, weekday_code),

    CONSTRAINT fk_museum_building_schedule_regular_building
        FOREIGN KEY (building_id)
        REFERENCES museum_buildings(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

`weekday_code`:

```text
mon
tue
wed
thu
fri
sat
sun
```

## 7.2. Исключения расписания

```sql
CREATE TABLE museum_building_schedule_exceptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    building_id INT UNSIGNED NOT NULL,

    exception_date DATE NOT NULL,
    time_begin TIME NULL,
    time_end TIME NULL,
    is_closed TINYINT(1) NOT NULL DEFAULT 0,

    UNIQUE KEY ux_museum_building_schedule_exception (building_id, exception_date),

    CONSTRAINT fk_museum_building_schedule_exceptions_building
        FOREIGN KEY (building_id)
        REFERENCES museum_buildings(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 8. Дополнительные учебные таблицы для интерфейса

Чтобы был не просто импорт, а нормальный учебный интерфейс, я бы добавил свои пользовательские таблицы.

## 8.1. Заметки к экспонатам

```sql
CREATE TABLE museum_masterpiece_notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    masterpiece_id INT UNSIGNED NOT NULL,

    title VARCHAR(255) NULL,
    note_text TEXT NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    KEY ix_museum_masterpiece_notes_masterpiece_id (masterpiece_id),

    CONSTRAINT fk_museum_masterpiece_notes_masterpiece
        FOREIGN KEY (masterpiece_id)
        REFERENCES museum_masterpieces(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 8.2. Теги

```sql
CREATE TABLE museum_tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name_ru VARCHAR(255) NOT NULL,

    UNIQUE KEY ux_museum_tags_name_ru (name_ru)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

```sql
CREATE TABLE museum_masterpiece_tags (
    masterpiece_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (masterpiece_id, tag_id),

    CONSTRAINT fk_museum_masterpiece_tags_masterpiece
        FOREIGN KEY (masterpiece_id)
        REFERENCES museum_masterpieces(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_museum_masterpiece_tags_tag
        FOREIGN KEY (tag_id)
        REFERENCES museum_tags(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Это даст учебный CRUD:

```text
добавить заметку
добавить тег
прикрепить тег к экспонату
отфильтровать экспонаты по тегам
```

---

# 9. Итоговая структура

Минимальный хороший набор:

```text
museum_masterpieces              главная таблица экспонатов

museum_types                     справочник типов
museum_countries                 справочник стран
museum_periods                   справочник периодов
museum_paint_schools             справочник школ
museum_graphics_types            справочник типов графики

museum_buildings                 здания
museum_floors                    этажи
museum_halls                     залы
museum_expositions               экспозиции
museum_exposition_halls          связь экспозиций и залов

museum_persons                   авторы, коллекционеры, мастера
museum_masterpiece_persons       связь экспонатов с персонами

museum_masterpiece_images        изображения экспонатов

museum_building_schedule_regular регулярное расписание
museum_building_schedule_exceptions исключения расписания

museum_tags                      пользовательские теги
museum_masterpiece_tags          связь тегов и экспонатов
museum_masterpiece_notes         пользовательские заметки
```

---

# 10. Что делать дальше по шагам

Я бы делал так:

1. Создать SQL-таблицы.
2. Написать импорт `buildings.json`.
3. Импортировать здания.
4. Импортировать этажи.
5. Импортировать залы.
6. Импортировать экспозиции.
7. Написать импорт `masterpieces.json`.
8. Сначала заполнить справочники: типы, страны, периоды, школы, типы графики.
9. Потом импортировать экспонаты.
10. Потом импортировать авторов, коллекционеров и связи.
11. Потом импортировать изображения.
12. После этого делать интерфейс.

Интерфейс для первого прохода:

```text
/masterpieces/index.php
```

Список экспонатов с фильтрами:

```text
тип
страна
период
здание
зал
автор
```

```text
/masterpieces/view.php?id=...
```

Карточка экспоната:

```text
название
год
инвентарный номер
тип
страна
период
материал
описание
галерея
авторы
коллекционеры
здание
зал
заметки
теги
```

```text
/buildings/index.php
```

Список зданий.

```text
/buildings/view.php?id=...
```

Карточка здания:

```text
описание
адрес
расписание
этажи
залы
экспозиции
экспонаты в этом здании
```

Главное: **не хранить авторов, коллекционеров, галерею и залы JSON-строкой в одной таблице**. Для учебного проекта лучше разнести их по связям — так ты реально потренируешь `Reference`, `OneToMany`, `ManyToMany` и нормальную ORM-архитектуру.


Да, в моём прошлом ответе я дал таблицы, но **не разложил их по типам связей**. Исправляю.

## 1. Связи один к одному — `1:1`

В исходных JSON **жёсткой обязательной 1:1 связи почти нет**. Большинство данных либо лежат прямо внутри объекта, либо являются `1:N` / `M:N`.

Но для учебного проекта можно специально сделать 1:1, чтобы потренировать ORM-связь.

### Вариант 1: экспонат → подробный контент

```text
museum_masterpieces 1:1 museum_masterpiece_details
```

То есть:

```text
museum_masterpieces
- id
- name_ru
- year
- inv_num
- building_id
- hall_id
```

А длинные текстовые поля вынести отдельно:

```text
museum_masterpiece_details
- masterpiece_id
- description_ru
- annotation_ru
- litra_ru
- restor_ru
- search_text_ru
- search_keywords_ru
```

Почему это 1:1:

```text
один экспонат имеет одну карточку подробного описания
одна карточка подробного описания относится только к одному экспонату
```

SQL-ключ:

```sql
masterpiece_id INT UNSIGNED PRIMARY KEY
```

То есть `masterpiece_id` одновременно и `PRIMARY KEY`, и `FOREIGN KEY`.

---

### Вариант 2: здание → подробный контент

```text
museum_buildings 1:1 museum_building_details
```

В `buildings.json` у здания много больших HTML-полей: `text.ru`, `textmore.ru`, `adress.ru`, `timeline.ru`, `rate.ru`, `tel.ru`, `rules.ru`, `audiog.ru`. В файле также есть вложенные структуры расписания и этажей. 

Можно сделать так:

```text
museum_buildings
- id
- source_id
- name_ru
- path
- is_closed
```

```text
museum_building_details
- building_id
- brief_ru
- text_ru
- text_more_ru
- address_ru
- timeline_ru
- rate_ru
- tel_ru
- rules_ru
- audioguide_ru
```

Это тоже учебная 1:1 связь.

---

## 2. Связи один ко многим — `1:N`

Это основные связи в твоей структуре.

---

### 2.1. Здание → этажи

```text
museum_buildings 1:N museum_floors
```

```text
одно здание имеет много этажей
один этаж принадлежит одному зданию
```

В JSON это видно по структуре:

```text
building
 └── floors
      └── floor_id
```

В `buildings.json` у здания есть вложенный блок `floors`, внутри которого находятся этажи и дальше залы. 

Ключ:

```sql
museum_floors.building_id
```

---

### 2.2. Этаж → залы

```text
museum_floors 1:N museum_halls
```

```text
один этаж имеет много залов
один зал принадлежит одному этажу
```

Ключ:

```sql
museum_halls.floor_id
```

---

### 2.3. Здание → залы

Это можно хранить напрямую тоже:

```text
museum_buildings 1:N museum_halls
```

```text
одно здание имеет много залов
один зал принадлежит одному зданию
```

Ключ:

```sql
museum_halls.building_id
```

Да, связь частично дублирует `floor_id`, но для удобных запросов это нормально в учебном проекте.

---

### 2.4. Зал → экспонаты

```text
museum_halls 1:N museum_masterpieces
```

```text
один зал содержит много экспонатов
один экспонат находится в одном зале
```

В `masterpieces.json` у экспоната есть поля:

```json
"hall": "186",
"building": "116"
```

Например, у экспоната есть `hall`, `building`, `gallery`, `authors`, `collectors`. 

Ключи:

```sql
museum_masterpieces.hall_id
museum_masterpieces.building_id
```

---

### 2.5. Здание → экспонаты

```text
museum_buildings 1:N museum_masterpieces
```

```text
одно здание содержит много экспонатов
один экспонат относится к одному зданию
```

Ключ:

```sql
museum_masterpieces.building_id
```

---

### 2.6. Тип → экспонаты

```text
museum_types 1:N museum_masterpieces
```

```text
один тип, например "Живопись", может быть у многих экспонатов
один экспонат имеет один основной тип
```

Ключ:

```sql
museum_masterpieces.type_id
```

---

### 2.7. Страна → экспонаты

```text
museum_countries 1:N museum_masterpieces
```

```text
одна страна может быть у многих экспонатов
один экспонат имеет одну основную страну
```

Ключ:

```sql
museum_masterpieces.country_id
```

---

### 2.8. Период → экспонаты

```text
museum_periods 1:N museum_masterpieces
```

```text
один период, например "XIX век", может быть у многих экспонатов
один экспонат имеет один основной период
```

Ключ:

```sql
museum_masterpieces.period_id
```

---

### 2.9. Экспонат → изображения

```text
museum_masterpieces 1:N museum_masterpiece_images
```

```text
один экспонат имеет много изображений
одно изображение принадлежит одному экспонату
```

В `masterpieces.json` поле `gallery` содержит группы изображений `id01`, `id02`, `id03`. У некоторых экспонатов несколько групп изображений. 

Ключ:

```sql
museum_masterpiece_images.masterpiece_id
```

---

### 2.10. Здание → регулярное расписание

```text
museum_buildings 1:N museum_building_schedule_regular
```

```text
одно здание имеет много строк расписания
одна строка расписания относится к одному зданию
```

Например:

```text
Понедельник
Вторник
Среда
Четверг
...
```

Ключ:

```sql
museum_building_schedule_regular.building_id
```

---

### 2.11. Здание → исключения расписания

```text
museum_buildings 1:N museum_building_schedule_exceptions
```

```text
одно здание имеет много исключений по датам
одно исключение относится к одному зданию
```

В `buildings.json` у расписания есть `regulars` и `exceptions`: регулярные дни недели и отдельные даты-исключения. 

Ключ:

```sql
museum_building_schedule_exceptions.building_id
```

---

## 3. Многие ко многим — `M:N`

Вот здесь появляются **связующие таблицы**.

---

## 3.1. Экспонаты ↔ персоны

Это главная связующая таблица для авторов и коллекционеров.

```text
museum_masterpieces M:N museum_persons
```

Через:

```text
museum_masterpiece_persons
```

Почему многие ко многим:

```text
один экспонат может иметь несколько авторов
один автор может иметь несколько экспонатов
```

И так же:

```text
один экспонат может иметь несколько коллекционеров
один коллекционер может быть связан с несколькими экспонатами
```

В `masterpieces.json` поля `authors` и `collectors` могут быть пустыми или объектами со списком персон; у автора может быть комментарий роли, например `автор`. 

Структура:

```text
museum_masterpieces
- id

museum_persons
- id
- name_ru

museum_masterpiece_persons
- masterpiece_id
- person_id
- role
- role_comment_ru
```

Пример `role`:

```text
author
collector
```

Это самая важная связующая таблица.

---

## 3.2. Экспозиции ↔ залы

```text
museum_expositions M:N museum_halls
```

Через:

```text
museum_exposition_halls
```

Почему многие ко многим:

```text
одна экспозиция может включать несколько залов
один зал теоретически может быть связан с несколькими экспозициями
```

В `buildings.json` у зала есть поле `exposition`, а у экспозиции может быть `halls_list`. Это хороший кандидат на связующую таблицу. 

Структура:

```text
museum_expositions
- id

museum_halls
- id

museum_exposition_halls
- exposition_id
- hall_id
```

---

## 3.3. Экспонаты ↔ теги

Это уже наша учебная пользовательская часть.

```text
museum_masterpieces M:N museum_tags
```

Через:

```text
museum_masterpiece_tags
```

Почему многие ко многим:

```text
один экспонат может иметь много тегов
один тег может быть у многих экспонатов
```

Например:

```text
Египет
портрет
скульптура
живопись
XIX век
избранное
на изучение
```

Структура:

```text
museum_masterpieces
- id

museum_tags
- id
- name_ru

museum_masterpiece_tags
- masterpiece_id
- tag_id
```

---

## 4. Общая схема связей

```text
museum_buildings
  1:N museum_floors
  1:N museum_halls
  1:N museum_masterpieces
  1:N museum_building_schedule_regular
  1:N museum_building_schedule_exceptions
  1:1 museum_building_details

museum_floors
  1:N museum_halls

museum_halls
  1:N museum_masterpieces
  M:N museum_expositions через museum_exposition_halls

museum_masterpieces
  1:1 museum_masterpiece_details
  1:N museum_masterpiece_images
  M:N museum_persons через museum_masterpiece_persons
  M:N museum_tags через museum_masterpiece_tags

museum_types
  1:N museum_masterpieces

museum_countries
  1:N museum_masterpieces

museum_periods
  1:N museum_masterpieces

museum_paint_schools
  1:N museum_masterpieces

museum_graphics_types
  1:N museum_masterpieces
```

---

## 5. Таблицы-связки

Вот именно **связующие таблицы**:

```text
museum_masterpiece_persons
```

Связь:

```text
экспонаты ↔ персоны
```

Для:

```text
авторы
коллекционеры
создатели
```

---

```text
museum_exposition_halls
```

Связь:

```text
экспозиции ↔ залы
```

---

```text
museum_masterpiece_tags
```

Связь:

```text
экспонаты ↔ пользовательские теги
```

---

Потенциально можно добавить ещё:

```text
museum_masterpiece_shop_items
```

Для поля `shop`, если захочешь связать экспонат с товарами магазина.

И:

```text
museum_hall_satellites
```

Для поля `satellites`, если выясним, что это связи между залами/объектами.

---

## 6. Самое важное для учебного ORM

Для изучения связей я бы сделал так:

| Тип связи | Пример                           | Таблицы                                              |
| --------- | -------------------------------- | ---------------------------------------------------- |
| `1:1`     | экспонат → подробное описание    | `museum_masterpieces` → `museum_masterpiece_details` |
| `1:N`     | здание → этажи                   | `museum_buildings` → `museum_floors`                 |
| `1:N`     | этаж → залы                      | `museum_floors` → `museum_halls`                     |
| `1:N`     | зал → экспонаты                  | `museum_halls` → `museum_masterpieces`               |
| `1:N`     | экспонат → картинки              | `museum_masterpieces` → `museum_masterpiece_images`  |
| `M:N`     | экспонаты ↔ авторы/коллекционеры | `museum_masterpiece_persons`                         |
| `M:N`     | экспозиции ↔ залы                | `museum_exposition_halls`                            |
| `M:N`     | экспонаты ↔ теги                 | `museum_masterpiece_tags`                            |

Главная учебная модель получается такая:

```text
BuildingTable
FloorTable
HallTable
MasterpieceTable
MasterpieceDetailTable
MasterpieceImageTable
PersonTable
MasterpiecePersonTable
TagTable
MasterpieceTagTable
```

И вот здесь ты сможешь нормально потренировать:

```text
Reference
OneToMany
ManyToMany
```

В Bitrix D7 ORM это как раз соответствует твоему учебному материалу: `Reference` для связи через внешний ключ, `OneToMany` для связи один-ко-многим, `ManyToMany` для связи многие-ко-многим через промежуточную таблицу. 
