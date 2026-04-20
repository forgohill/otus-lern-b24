<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->SetTitle('Books - интерфейс');
Asset::getInstance()->addCss('/local/sandbox/style.css');

if (Loader::includeModule('ui')) {
 Extension::load([
  'ui.buttons',
  'ui.fonts.opensans',
 ]);
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/modules/BooksForLessons/install/db/mysql/index.php';

$installResult = null;
$booksTableExists = false;

global $DB;
$booksTableExists = (bool)$DB->Query("SHOW TABLES LIKE 'books'")->Fetch();

if (
 $_SERVER['REQUEST_METHOD'] === 'POST'
 && isset($_POST['install_books_table'])
 && check_bitrix_sessid()
) {
 if (!$booksTableExists) {
  $installer = new BooksForLessons();
  $installResult = $installer->InstallDB();
  $booksTableExists = !empty($installResult['success']);
 } else {
  $installResult = [
   'success' => true,
   'message' => 'Таблица books уже существует.',
  ];
 }
}

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

   <form method="post" class="sandbox-study-card" style="margin-bottom: 20px;">
    <?= bitrix_sessid_post() ?>
    <div class="sandbox-study-badge">Installer</div>
    <h2 class="sandbox-study-title">Установка таблиц books</h2>
    <p class="sandbox-study-text">
     Нажми кнопку, чтобы выполнить SQL-файл установки в текущей базе данных.
    </p>
    <?php if ($booksTableExists): ?>
     <p class="sandbox-study-note" style="margin-bottom: 12px;">
      Таблица <code>books</code> уже создана, повторная установка отключена.
     </p>
    <?php endif; ?>
    <button type="submit" name="install_books_table" value="Y" class="ui-btn ui-btn-success ui-btn-round" <?= $booksTableExists ? 'disabled' : '' ?>>
     Установить
    </button>
   </form>

   <?php if (is_array($installResult)): ?>
    <div class="sandbox-status <?= !empty($installResult['success']) ? 'sandbox-status--success' : 'sandbox-status--error' ?>">
     <?= htmlspecialcharsbx((string)($installResult['message'] ?? '')) ?>
    </div>

    <?php if (!empty($installResult['errors'])): ?>
     <div class="sandbox-study-card">
      <div class="sandbox-study-badge">Ошибки</div>
      <pre class="sandbox-study-code"><code><?= htmlspecialcharsbx(print_r($installResult['errors'], true)) ?></code></pre>
     </div>
    <?php endif; ?>
   <?php endif; ?>

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
