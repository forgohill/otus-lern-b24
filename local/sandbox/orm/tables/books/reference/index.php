<?php

use Bitrix\Main\Page\Asset;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->SetTitle('Справочник по таблицам');
Asset::getInstance()->addCss('/local/sandbox/style.css');

$referenceRows = [
 [
  'section' => 'Основная таблица',
  'title' => 'books',
  'description' => 'Таблица с книгами. Здесь хранится основная карточка книги и внешние ключи на связанные сущности.',
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
  'section' => 'Связанные таблицы',
  'title' => 'authors / publishers / wikiprofiles',
  'description' => 'Справочники, на которые ссылается books через связи ORM.',
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
  'section' => 'Связующие таблицы',
  'title' => 'book_author / book_publisher / book_store',
  'description' => 'Промежуточные таблицы для связей многие-ко-многим и привязки книг к магазинам.',
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
  <a href="/local/sandbox/orm/tables/books/" class="ui-btn ui-btn-light-border ui-btn-round">Назад</a>
 </div>

 <section class="sandbox-study-section">
  <div class="sandbox-study-header">Справочник по таблицам</div>
  <div class="sandbox-study-body">
   <div class="sandbox-study-code">
    <table class="sandbox-reference-table">
     <thead>
      <tr>
       <th>Раздел</th>
       <th>Таблица</th>
       <th>Описание</th>
       <th>Поля</th>
      </tr>
     </thead>
     <tbody>
      <?php foreach ($referenceRows as $row): ?>
       <tr>
        <td><?= htmlspecialcharsbx($row['section']) ?></td>
        <td><?= htmlspecialcharsbx($row['title']) ?></td>
        <td><?= htmlspecialcharsbx($row['description']) ?></td>
        <td>
         <?= htmlspecialcharsbx(implode(', ', $row['fields'])) ?>
        </td>
       </tr>
      <?php endforeach; ?>
     </tbody>
    </table>
   </div>
  </div>
 </section>
</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
