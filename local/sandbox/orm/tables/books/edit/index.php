<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Type\Date;
use Models\BooksForLessons\BooksTable as Books;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

Loader::includeModule('books');
Loader::includeModule('authors');
Loader::includeModule('publishers');
Loader::includeModule('stores');
Loader::includeModule('book_publisher');
Loader::includeModule('book_store');
Loader::includeModule('wikiprofiles');
Loader::includeModule('book_author');

require_once __DIR__ . '/../crud/BooksTableReader.php';

$bookId = (int)($_GET['ID'] ?? 0);
$booksTableReader = new BooksTableReader();
$book = $booksTableReader->getBookById($bookId);

$errors = [];
$successMessage = '';

if (Loader::includeModule('ui')) {
 Extension::load([
  'main.popup',
  'ui.buttons',
  'ui.fonts.opensans',
  'ui.icon-set.main',
 ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 if (!check_bitrix_sessid()) {
  $errors[] = 'Сессия истекла. Обновите страницу и отправьте форму еще раз.';
 } elseif ($book === null) {
  $errors[] = 'Книга не найдена.';
 } else {
  $fields = [
   'name' => trim((string)($_POST['NAME'] ?? '')),
   'text' => trim((string)($_POST['TEXT'] ?? '')),
   'publish_date' => trim((string)($_POST['PUBLISH_DATE'] ?? '')) !== ''
    ? new Date(trim((string)($_POST['PUBLISH_DATE'] ?? '')), 'Y-m-d')
    : null,
   'ISBN' => trim((string)($_POST['ISBN'] ?? '')),
   'author_id' => (int)($_POST['AUTHOR_ID'] ?? 0) ?: null,
   'publisher_id' => (int)($_POST['PUBLISHER_ID'] ?? 0) ?: null,
   'wikiprofile_id' => (int)($_POST['WIKIPROFILE_ID'] ?? 0) ?: null,
  ];

  $result = Books::update($bookId, $fields);

  if ($result->isSuccess()) {
   $successMessage = 'Книга обновлена.';
   $book = $booksTableReader->getBookById($bookId);
  } else {
   foreach ($result->getErrorMessages() as $errorMessage) {
    $errors[] = $errorMessage;
   }
  }
 }
}

$APPLICATION->SetTitle('Редактирование книги');
Asset::getInstance()->addCss('/local/sandbox/style.css');
Asset::getInstance()->addJs('/local/sandbox/orm/tables/books/edit/index.js');

$fieldRows = $book !== null ? [
 [
  'key' => 'NAME',
  'label' => 'Название',
  'type' => 'text',
  'value' => (string)$book->getName(),
 ],
 [
  'key' => 'TEXT',
  'label' => 'Описание',
  'type' => 'textarea',
  'value' => (string)$book->getText(),
 ],
 [
  'key' => 'PUBLISH_DATE',
  'label' => 'Дата выхода',
  'type' => 'date',
  'value' => $book->getPublishDate()?->format('Y-m-d') ?? '',
 ],
 [
  'key' => 'ISBN',
  'label' => 'ISBN',
  'type' => 'text',
  'value' => (string)$book->getIsbn(),
 ],
 [
  'key' => 'AUTHOR_ID',
  'label' => 'ID автора',
  'type' => 'number',
  'value' => (string)($book->getAuthorId() ?? ''),
 ],
 [
  'key' => 'PUBLISHER_ID',
  'label' => 'ID издателя',
  'type' => 'number',
  'value' => (string)($book->getPublisherId() ?? ''),
 ],
 [
  'key' => 'WIKIPROFILE_ID',
  'label' => 'ID wiki-профиля',
  'type' => 'number',
  'value' => (string)($book->getWikiprofileId() ?? ''),
 ],
] : [];
?>

<div class="sandbox-study sandbox-book-edit-page">
 <div class="sandbox-top-actions">
  <a href="/local/sandbox/orm/tables/books/" class="ui-btn ui-btn-light-border ui-btn-round">Назад</a>
 </div>

 <section class="sandbox-study-section">
  <div class="sandbox-study-header">Редактирование книги</div>
  <div class="sandbox-study-body">
   <?php if ($successMessage !== ''): ?>
    <div class="sandbox-status sandbox-status--success sandbox-status--compact">
     <?= htmlspecialcharsbx($successMessage) ?>
    </div>
   <?php endif; ?>

   <?php if ($errors): ?>
    <div class="sandbox-status sandbox-status--error sandbox-status--compact">
     <?php foreach ($errors as $error): ?>
      <div><?= htmlspecialcharsbx($error) ?></div>
     <?php endforeach; ?>
    </div>
   <?php endif; ?>

   <?php if ($book !== null): ?>
    <div class="sandbox-book-edit-hero">
     <div>
      <div class="sandbox-book-edit-kicker">ID <?= (int)$bookId ?></div>
      <h1 class="sandbox-book-edit-title"><?= htmlspecialcharsbx((string)$book->getName()) ?></h1>
      <div class="sandbox-book-edit-subtitle">
       <?= htmlspecialcharsbx((string)$book->getPublisher()?->getName()) ?>
      </div>
     </div>
    </div>

    <form method="post" action="" class="sandbox-book-form">
     <?= bitrix_sessid_post() ?>

     <?php foreach ($fieldRows as $field): ?>
      <div class="sandbox-book-field">
       <div class="sandbox-book-field-head">
        <label class="sandbox-book-field-label" for="book-field-<?= htmlspecialcharsbx($field['key']) ?>">
         <?= htmlspecialcharsbx($field['label']) ?>
        </label>
        <button
         type="button"
         class="ui-btn ui-btn-light-border ui-btn-round sandbox-book-field-edit-btn"
         data-book-field-edit
         data-book-field-target="#book-field-<?= htmlspecialcharsbx($field['key']) ?>"
         data-book-field-label="<?= htmlspecialcharsbx($field['label']) ?>"
         data-book-field-type="<?= htmlspecialcharsbx($field['type']) ?>"
        >
         <span class="ui-icon-set --edit-pencil sandbox-book-field-edit-icon"></span>
         <span>Редактировать</span>
        </button>
       </div>

       <?php if ($field['type'] === 'textarea'): ?>
        <textarea
         id="book-field-<?= htmlspecialcharsbx($field['key']) ?>"
         name="<?= htmlspecialcharsbx($field['key']) ?>"
         class="sandbox-book-field-input sandbox-book-field-textarea"
         rows="4"
        ><?= htmlspecialcharsbx($field['value']) ?></textarea>
       <?php else: ?>
        <input
         id="book-field-<?= htmlspecialcharsbx($field['key']) ?>"
         name="<?= htmlspecialcharsbx($field['key']) ?>"
         type="<?= htmlspecialcharsbx($field['type']) ?>"
         value="<?= htmlspecialcharsbx($field['value']) ?>"
         class="sandbox-book-field-input"
        >
       <?php endif; ?>
      </div>
     <?php endforeach; ?>

     <div class="sandbox-book-form-actions">
      <button type="submit" class="ui-btn ui-btn-success ui-btn-round">Сохранить</button>
     </div>
    </form>
   <?php else: ?>
    <div class="sandbox-study-note">
     Книга не найдена.
    </div>
   <?php endif; ?>
  </div>
 </section>
</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
