<?php

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;

use Models\BooksForLessons\AuthorsTable as Authors;
use Models\BooksForLessons\BookAuthorTable as BookAuthor;
use Models\BooksForLessons\BookPublisherTable as BookPublisher;
use Models\BooksForLessons\BooksTable as Books;
use Models\BooksForLessons\BookStoreTable as BookStore;
use Models\BooksForLessons\PublishersTable as Publishers;
use Models\BooksForLessons\StoresTable as Stores;
use Models\BooksForLessons\WikiprofilesTable as Wikiprofiles;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetTitle('Books - интерфейс');

Loader::includeModule('books');
Loader::includeModule('authors');
Loader::includeModule('publishers');
Loader::includeModule('stores');
Loader::includeModule('book_publisher');
Loader::includeModule('book_store');
Loader::includeModule('wikiprofiles');
Loader::includeModule('book_author');

$booksCollection = Books::getList([
 'select' => [
  '*',
  'AUTHORS',
  'PUBLISHER',
  'WIKIPROFILE',
 ]
])->fetchCollection();

$booksCollectionItem = [];
if ($booksCollection !== null) {
 foreach ($booksCollection as $key => $bookItem) {

  $authors = [];
  foreach ($bookItem->getAuthors() as $key => $author) {
   $authors[] = [
    'AUTHOR_ID' => $author->getId(),
    'AUTHOR_NAME' => $author->getName(),
   ];
  }
  $booksCollectionItem[] = [
   'ID' => $bookItem->getId(),
   'NAME' => $bookItem->getName(),
   'TEXT' => $bookItem->getText(),
   'PUBLISH_DATE' => $bookItem->getPublishDate()?->format('Y-m-d'),
   'ISBN' => $bookItem->getIsbn(),
   'AUTHORS' => $authors,
   'PUBLISHER_ID' => $bookItem->getPublisher()->getId(),
   'PUBLISHER_NAME' => $bookItem->getPublisher()->getName(),
   'WIKIPROFILE_ID' => $bookItem->getWikiprofile()?->getId(),
   'WIKIPROFILE_RU' => $bookItem->getWikiprofile()?->getWikiprofileRu(),
  ];
 }

 // dump($booksCollectionItem);
}

Asset::getInstance()->addCss('/local/sandbox/style.css');

$schemaCards = [
 [
  'badge' => 'Основная таблица',
  'title' => 'books',
  'text' => 'Таблица с книгами. Здесь позже можно подключить ORM-класс и вывести список записей.',
  'fields' => [
   'id',
   'name',
   'text',
   'publish_date',
   'ISBN',
   'author_id',
   'publisher_id',
   'wikiprofile_id',
  ],
 ],
 [
  'badge' => 'Связанные таблицы',
  'title' => 'authors / publishers / wikiprofiles',
  'text' => 'Справочники, на которые ссылается books через внешние ключи и связи.',
  'fields' => [
   'authors.id',
   'authors.name',
   'publishers.id',
   'publishers.name',
   'wikiprofiles.id',
   'wikiprofiles.wikiprofile_ru',
   'wikiprofiles.wikiprofile_en',
   'wikiprofiles.book_id',
  ],
 ],
 [
  'badge' => 'Связующие таблицы',
  'title' => 'book_author / book_publisher / book_store',
  'text' => 'Промежуточные таблицы для связей многие-ко-многим и привязки книг к магазинам.',
  'fields' => [
   'book_author.book_id',
   'book_author.author_id',
   'book_publisher.book_id',
   'book_publisher.publisher_id',
   'book_store.book_id',
   'book_store.store_id',
  ],
 ],
];

?>

<div class="sandbox-study">
 <div class="sandbox-top-actions">
  <a href="/local/sandbox/index.php" class="ui-btn ui-btn-light-border ui-btn-round">Назад</a>
 </div>

 <section class="sandbox-study-section">
  <div class="sandbox-study-header">Books</div>
  <div class="sandbox-study-body">
   <p class="sandbox-study-intro">
    Это интерфейсная заглушка для будущей ORM-песочницы по таблице <code>books</code>.
    Классы и запросы ты подключишь отдельно, здесь только структура страницы и ориентир по схеме.
   </p>

   <?php foreach ($schemaCards as $card): ?>
    <div class="sandbox-study-card">
     <div class="sandbox-study-badge"><?= htmlspecialcharsbx($card['badge']) ?></div>
     <h2 class="sandbox-study-title"><?= htmlspecialcharsbx($card['title']) ?></h2>
     <p class="sandbox-study-text"><?= htmlspecialcharsbx($card['text']) ?></p>
     <div class="sandbox-study-code">
      <code><?= htmlspecialcharsbx(implode("\n", $card['fields'])) ?></code>
     </div>
    </div>
   <?php endforeach; ?>

   <p class="sandbox-study-note">
    Когда подключишь ORM-класс, сюда можно добавить выборку книг, фильтры, связь с авторами и вывод связанных сущностей.
   </p>
  </div>
 </section>
</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
