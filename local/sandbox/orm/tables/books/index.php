<?php

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;

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

if (Loader::includeModule('ui')) {
 Extension::load([
  'ui.buttons',
  'ui.fonts.opensans',
  'ui.icon-set.main',
 ]);
}

require_once __DIR__ . '/crud/BooksTableReader.php';

$booksTableReader = new BooksTableReader();
$booksCollectionItem = $booksTableReader->getBooksCollectionItems();
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

   <div class="sandbox-book-grid">
    <?php if (!empty($booksCollectionItem)): ?>
     <?php foreach ($booksCollectionItem as $book): ?>
      <article class="sandbox-book-card">
       <div class="sandbox-book-card-header">
        <div>
         <h2 class="sandbox-book-title"><?= htmlspecialcharsbx($book['NAME']) ?></h2>
         <div class="sandbox-book-subtitle">
          <?= htmlspecialcharsbx($book['PUBLISHER_NAME'] ?? '') ?>
         </div>
        </div>
        <a
         href="/local/sandbox/orm/tables/books/edit/?ID=<?= (int)$book['ID'] ?>"
         class="ui-btn ui-btn-light-border ui-btn-round sandbox-book-edit-btn"
        >
         <span class="ui-icon-set --edit-pencil sandbox-book-edit-icon"></span>
         <span>Редактировать</span>
        </a>
       </div>

       <p class="sandbox-book-text">
        <?= htmlspecialcharsbx($book['TEXT'] ?? '') ?>
       </p>

       <dl class="sandbox-book-meta">
        <div>
         <dt>Дата выхода</dt>
         <dd><?= htmlspecialcharsbx($book['PUBLISH_DATE'] ?? '') ?></dd>
        </div>
        <div>
         <dt>ISBN</dt>
         <dd><?= htmlspecialcharsbx($book['ISBN'] ?? '') ?></dd>
        </div>
        <div>
         <dt>Авторы</dt>
         <dd>
          <?php if (!empty($book['AUTHORS'])): ?>
           <?= htmlspecialcharsbx(implode(', ', array_column($book['AUTHORS'], 'AUTHOR_NAME'))) ?>
          <?php else: ?>
           Нет авторов
          <?php endif; ?>
         </dd>
        </div>
        <div>
         <dt>Магазины</dt>
         <dd>
          <?php if (!empty($book['STORES'])): ?>
           <?= htmlspecialcharsbx(implode(', ', array_column($book['STORES'], 'STORE_NAME'))) ?>
          <?php else: ?>
           Нет магазинов
          <?php endif; ?>
         </dd>
        </div>
        <div>
         <dt>Ссылка на вики</dt>
         <dd>
          <a href="<?= htmlspecialcharsbx($book['WIKIPROFILE_RU'] ?? '') ?>"
           target="_blank">
           <?= htmlspecialcharsbx($book['WIKIPROFILE_RU'] ?? '') ?>
          </a>

         </dd>
        </div>
       </dl>
      </article>
     <?php endforeach; ?>
    <?php else: ?>
     <div class="sandbox-study-note">
      Данные по книгам пока не найдены.
     </div>
    <?php endif; ?>
   </div>

   <p class="sandbox-study-note">
    Когда подключишь ORM-класс, сюда можно добавить выборку книг, фильтры, связь с авторами и вывод связанных сущностей.
   </p>
  </div>
 </section>
</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
