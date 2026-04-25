<?php

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;

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

require_once __DIR__ . '/crud/BooksTableReader.php';

$booksTableReader = new BooksTableReader();
$booksCollectionItem = $booksTableReader->getBooksCollectionItems();
dump($booksCollectionItem);
Asset::getInstance()->addCss('/local/sandbox/style.css');

?>

<div class="sandbox-study">
 <div class="sandbox-top-actions">
  <a href="/local/sandbox/index.php" class="ui-btn ui-btn-light-border ui-btn-round">Назад</a>
  <a href="/local/sandbox/orm/tables/books/reference/" class="ui-btn ui-btn-primary ui-btn-round">Справочник по таблицам</a>
 </div>

 <section class="sandbox-study-section">
  <div class="sandbox-study-header">Books</div>
  <div class="sandbox-study-body">
   <p class="sandbox-study-intro">
    Это интерфейсная заглушка для будущей ORM-песочницы по таблице <code>books</code>.
    Классы и запросы ты подключишь отдельно, здесь только структура страницы и ориентир по схеме.
   </p>

   <p class="sandbox-study-note">
    Когда подключишь ORM-класс, сюда можно добавить выборку книг, фильтры, связь с авторами и вывод связанных сущностей.
   </p>
  </div>
 </section>
</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
