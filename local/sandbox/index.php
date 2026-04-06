<?php

use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->SetTitle('Песочница');

if (Loader::includeModule('ui')) {
 Extension::load([
  'ui.buttons',
  'ui.fonts.opensans',
 ]);
}

$sandboxSections = [
 [
  'title' => 'API',
  'items' => [
   [
    'label' => 'Cars',
    'description' => 'Через взаимодействие с инфоблоками Cars.',
    'url' => '/local/sandbox/api/cars/',
   ],
   // [
   //  'label' => 'Формы',
   //  'description' => 'Проверка полей ввода, форм и поведения интерфейса.',
   //  'url' => '/local/sandbox/ui/forms/',
   // ],
  ],
 ],
 // [
 //  'title' => 'D7 и ядро',
 //  'items' => [
 //   [
 //    'label' => 'Loader',
 //    'description' => 'Проверка подключения модулей и базовых возможностей ядра.',
 //    'url' => '/local/sandbox/d7/loader/',
 //   ],
 //   [
 //    'label' => 'ORM',
 //    'description' => 'Эксперименты с D7 ORM и выборками.',
 //    'url' => '/local/sandbox/d7/orm/',
 //   ],
 //  ],
 // ],
 // [
 //  'title' => 'Инфоблоки',
 //  'items' => [
 //   [
 //    'label' => 'Список элементов',
 //    'description' => 'Тесты получения и вывода элементов инфоблока.',
 //    'url' => '/local/sandbox/iblock/list/',
 //   ],
 //   [
 //    'label' => 'Добавление элемента',
 //    'description' => 'Проверка создания элементов и записи свойств.',
 //    'url' => '/local/sandbox/iblock/add/',
 //   ],
 //  ],
 // ],
 // [
 //  'title' => 'REST / CRM',
 //  'items' => [
 //   [
 //    'label' => 'REST запросы',
 //    'description' => 'Песочница для проверки REST-вызовов.',
 //    'url' => '/local/sandbox/rest/test/',
 //   ],
 //   [
 //    'label' => 'CRM',
 //    'description' => 'Локальные тесты сущностей CRM.',
 //    'url' => '/local/sandbox/crm/test/',
 //   ],
 //  ],
 // ],
 // [
 //  'title' => 'Отладка',
 //  'items' => [
 //   [
 //    'label' => 'Debug',
 //    'description' => 'Быстрые проверки, дампы, служебные тесты.',
 //    'url' => '/local/sandbox/debug/',
 //   ],
 //  ],
 // ],
];

?>

<style>
 html {
  scroll-behavior: smooth;
 }

 body {
  font-family: "Open Sans", Arial, sans-serif;
 }

 .sandbox-page {
  max-width: 1100px;
  margin: 0 auto;
  padding: 24px 16px 48px;
 }

 .sandbox-hero {
  background: #f8fafc;
  border: 1px solid #dfe5ec;
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 24px;
 }

 .sandbox-title {
  margin: 0 0 12px;
  font-size: 28px;
  line-height: 36px;
  font-weight: 700;
  color: #1f2d3d;
 }

 .sandbox-text {
  margin: 0;
  font-size: 15px;
  line-height: 24px;
  color: #525c69;
  max-width: 820px;
 }

 .sandbox-section {
  background: #ffffff;
  border: 1px solid #dfe5ec;
  border-radius: 16px;
  margin-bottom: 24px;
  overflow: hidden;
 }

 .sandbox-section-header {
  padding: 18px 24px;
  border-bottom: 1px solid #eef2f4;
  font-size: 20px;
  line-height: 28px;
  font-weight: 600;
  color: #1f2d3d;
  background: #fff;
 }

 .sandbox-section-body {
  padding: 24px;
 }

 .sandbox-actions {
  margin: 0;
  padding: 0;
  list-style: none;
  border: 1px solid #eef2f4;
  border-radius: 12px;
  overflow: hidden;
 }

 .sandbox-actions-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  padding: 16px 18px;
  border-bottom: 1px solid #eef2f4;
  background: #fff;
 }

 .sandbox-actions-item:last-child {
  border-bottom: none;
 }

 .sandbox-actions-content {
  flex: 1;
 }

 .sandbox-actions-label {
  margin: 0 0 4px;
  font-size: 15px;
  line-height: 22px;
  font-weight: 600;
  color: #2f3b47;
 }

 .sandbox-actions-description {
  margin: 0;
  font-size: 14px;
  line-height: 21px;
  color: #6b7280;
 }

 .sandbox-top-actions {
  margin-bottom: 16px;
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
 }

 @media (max-width: 768px) {
  .sandbox-title {
   font-size: 24px;
   line-height: 32px;
  }

  .sandbox-actions-item {
   flex-direction: column;
   align-items: flex-start;
  }
 }
</style>

<div class="sandbox-page">
 <div class="sandbox-top-actions">
  <a href="/homeworks/index.php" class="ui-btn ui-btn-light-border ui-btn-round">Назад</a>
 </div>

 <div class="sandbox-hero">
  <h1 class="sandbox-title">Песочница</h1>
  <p class="sandbox-text">
   Раздел для локальных тестовых страниц и экспериментов с возможностями Bitrix24.
   Ниже находятся ссылки на отдельные песочницы по темам.
  </p>
 </div>

 <?php foreach ($sandboxSections as $section): ?>
  <section class="sandbox-section">
   <div class="sandbox-section-header"><?= htmlspecialcharsbx($section['title']) ?></div>
   <div class="sandbox-section-body">
    <ul class="sandbox-actions">
     <?php foreach ($section['items'] as $item): ?>
      <li class="sandbox-actions-item">
       <div class="sandbox-actions-content">
        <p class="sandbox-actions-label"><?= htmlspecialcharsbx($item['label']) ?></p>
        <p class="sandbox-actions-description"><?= htmlspecialcharsbx($item['description']) ?></p>
       </div>
       <a href="<?= htmlspecialcharsbx($item['url']) ?>" class="ui-btn ui-btn-primary ui-btn-round">
        Открыть
       </a>
      </li>
     <?php endforeach; ?>
    </ul>
   </div>
  </section>
 <?php endforeach; ?>
</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
