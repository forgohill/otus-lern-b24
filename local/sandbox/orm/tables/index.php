<?php

use Bitrix\Main\Page\Asset;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->SetTitle('ORM - таблицы');
Asset::getInstance()->addCss('/local/sandbox/style.css');

$sections = [
 [
  'label' => 'Books',
  'description' => 'Учебная модель книг и связей между ORM-сущностями.',
  'url' => '/local/sandbox/orm/tables/books/',
 ],
 [
  'label' => 'CRUD',
  'description' => 'Примеры добавления, чтения, обновления и удаления записей.',
  'url' => '/local/sandbox/orm/tables/crud/',
 ],
 [
  'label' => 'HospitalClients',
  'description' => 'Пример описания своей таблицы и связи с CRM-контактом.',
  'url' => '/local/sandbox/orm/tables/HospitalClients/',
 ],
];
?>

<div class="sandbox-page">
 <div class="sandbox-top-actions">
  <a href="/local/sandbox/index.php" class="ui-btn ui-btn-light-border ui-btn-round">Назад</a>
 </div>

 <div class="sandbox-hero">
  <h1 class="sandbox-title">ORM - таблицы</h1>
  <p class="sandbox-text">
   Раздел с примерами работы с таблицами БД через D7 ORM: описание сущностей, связи и CRUD.
  </p>
 </div>

 <section class="sandbox-section">
  <div class="sandbox-section-header">Доступные страницы</div>
  <div class="sandbox-section-body">
   <ul class="sandbox-actions">
    <?php foreach ($sections as $section): ?>
     <li class="sandbox-actions-item">
      <div class="sandbox-actions-content">
       <p class="sandbox-actions-label"><?= htmlspecialcharsbx($section['label']) ?></p>
       <p class="sandbox-actions-description"><?= htmlspecialcharsbx($section['description']) ?></p>
      </div>
      <a href="<?= htmlspecialcharsbx($section['url']) ?>" class="ui-btn ui-btn-primary ui-btn-round">
       Открыть
      </a>
     </li>
    <?php endforeach; ?>
   </ul>
  </div>
 </section>
</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
