create table books (
	id INTEGER NOT NULL auto_increment PRIMARY KEY,
	name VARCHAR(50),
	text TEXT,
	publish_date DATE,
	ISBN VARCHAR(50),
	author_id TINYINT(4),
	publisher_id INT(11),
	wikiprofile_id INT(11)
);

insert into books (id, name, text, publish_date, ISBN, author_id, publisher_id, wikiprofile_id) values (1, 'Vue.js и Laravel создание SPA приложений', 'Книга содержит подробное описание Vue.js - библиотеки JS', '2024-05-01', '978-5-1243-3454-1', 1, 1, 0);
insert into books (id, name, text, publish_date, ISBN, author_id, publisher_id, wikiprofile_id) values (2, 'Оно', 'Роман написанный в жанре ужасов', '1986-05-01', '978-5-1235-2435-2', 2, 2, 1);
insert into books (id, name, text, publish_date, ISBN, author_id, publisher_id, wikiprofile_id) values (3, 'Мгла', 'Книга вошла в десятку лучших произведений в жанре ужасов', '1980-05-01', '978-5-4366-4234-3', 2, 1, 3);
insert into books (id, name, text, publish_date, ISBN, author_id, publisher_id, wikiprofile_id) values (4, 'Космос', 'Научно-популярная книга охватывает широкий круг тем области антропологии, космологии, биологии.', '2017-05-01','978-5-3464-7534-4', 3, 1, 0);
insert into books (id, name, text, publish_date, ISBN, author_id, publisher_id, wikiprofile_id) values (5, 'Путеводитель по Node.js', 'нига содержит подробное описание технологии Node.js', '2017-05-01', '978-5-1453-6846-5', 1, 2, 0);
insert into books (id, name, text, publish_date, ISBN, author_id, publisher_id, wikiprofile_id) values (6, 'Кладбище домашних животных', 'Роман написанн о страшном месте.', '1983-05-01', '978-5-6785-8657-6', 1, 2, 2);


create table authors (
	id INTEGER NOT NULL auto_increment PRIMARY KEY,
	name VARCHAR(50)
);

insert into authors (id, name) values (1,'Эван Ю');
insert into authors (id, name) values (2, 'Стивен Кинг');
insert into authors (id, name) values (3, 'Карл Саган');


create table publishers (
	id INTEGER NOT NULL auto_increment PRIMARY KEY,
	name VARCHAR(50)
);

insert into publishers (id, name) values (1,'Британская академия наук');
insert into publishers (id, name) values (2, 'Viking Press');


create table stores (
	id INTEGER NOT NULL auto_increment PRIMARY KEY,
	name VARCHAR(50)
);

insert into stores (id, name) values (1,'Книголюб');
insert into stores (id, name) values (2, 'Читайка');


create table book_publisher (
	id INTEGER NOT NULL auto_increment PRIMARY KEY,
	book_id INT(11),
	publisher_id INT(11)
);

insert into book_publisher (id, book_id, publisher_id) values (1,1,1);
insert into book_publisher (id, book_id, publisher_id) values (2,2,2);
insert into book_publisher (id, book_id, publisher_id) values (3,2,2);
insert into book_publisher (id, book_id, publisher_id) values (4,2,2);
insert into book_publisher (id, book_id, publisher_id) values (5,4,2);
insert into book_publisher (id, book_id, publisher_id) values (7,6,2);

create table book_store (
	id INTEGER NOT NULL auto_increment PRIMARY KEY,
	book_id INT(11),
	store_id INT(11)
);

insert into book_store (id, book_id, store_id) values (1,1,2);
insert into book_store (id, book_id, store_id) values (2,2,1);
insert into book_store (id, book_id, store_id) values (3,2,2);


create table wikiprofiles (
	id INTEGER NOT NULL auto_increment PRIMARY KEY,
	wikiprofile_ru VARCHAR(50),
	wikiprofile_en VARCHAR(50),
	book_id INT(11)
);

insert into wikiprofiles (id, wikiprofile_ru, wikiprofile_en, book_id) values (1,'https://ru.wikipedia.org/wiki/Оно_(роман)','https://en.wikipedia.org/wiki/It_(novel)',2);
insert into wikiprofiles (id, wikiprofile_ru, wikiprofile_en, book_id) values (2,'https://ru.wikipedia.org/wiki/Кладбище_домашних_животных_(роман','https://en.wikipedia.org/wiki/Pet_Sematary',6);
insert into wikiprofiles (id, wikiprofile_ru, wikiprofile_en, book_id) values (3,'https://ru.wikipedia.org/wiki/Туман_(повесть)','https://en.wikipedia.org/wiki/The_Mist_(novella)',3);


create table book_author (
	id INTEGER NOT NULL auto_increment PRIMARY KEY,
	book_id INT(11),
	author_id INT(11)
);

insert into book_author (id, book_id, author_id) values (1,2,2);
insert into book_author (id, book_id, author_id) values (2,3,2);
insert into book_author (id, book_id, author_id) values (3,4,3);
