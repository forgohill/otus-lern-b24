<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->SetTitle('Песочница');
Asset::getInstance()->addCss('/local/sandbox/style.css');

if (Loader::includeModule('ui')) {
 Extension::load([
  'ui.buttons',
  'ui.fonts.opensans',
 ]);
}

$sandboxSections = [
 [
  'title' => 'Таблицы БД через ORM',
  'items' => [
   [
    'label' => 'Описание таблицы',
    'description' => 'DataManager-класс, карта полей и подключение собственной таблицы БД.',
    'url' => '/local/sandbox/orm/tables/',
   ],
   [
    'label' => 'Books: интерфейс',
    'description' => 'Заготовка страницы для схемы books и связанных таблиц без подключения ORM-класса.',
    'url' => '/local/sandbox/orm/tables/books/',
   ],
   [
    'label' => 'CRUD-запросы',
    'description' => 'Добавление, выборка, обновление и удаление записей через D7 ORM.',
    'url' => '/local/sandbox/orm/tables/crud/',
   ],
  ],
 ],
 [
  'title' => 'Инфоблоки - списки',
  'items' => [
   [
    'label' => 'OLD API',
    'description' => 'Через взаимодействие с инфоблоками Cars.',
    'url' => '/local/sandbox/api/cars/',
   ],
   [
    'label' => 'ORM',
    'description' => 'Через взаимодействие с инфоблоками Cars.',
    'url' => '/local/sandbox/orm/cars/',
   ],
   [
    'label' => 'Связывание моделей',
    'description' => 'Тесты связывания моделей через инфоблоки Cars.',
    'url' => '/local/sandbox/multi/cars/',
   ],
   // [
   //  'label' => 'Формы',
   //  'description' => 'Проверка полей ввода, форм и поведения интерфейса.',
   //  'url' => '/local/sandbox/ui/forms/',
   // ],
  ],
 ],
 [
  'title' => 'CRM',
  'items' => [
   [
    'label' => 'Смарт процессы',
    'description' => 'Получение с-п \ фабрики.',
    'url' => '/local/sandbox/crm/first-sp/',
   ],
   [
    'label' => 'Аналог DaData',
    'description' => 'Поиск реквизитов по ИНН.',
    'url' => '/local/sandbox/crm/first-sp/index-dadata.php',
   ],
  ],
 ],
 [
  'title' => 'HL-блоки - справочники',
  'items' => [
   [
    'label' => 'PantoneColors: список',
    'description' => 'Чтение элементов HL-блока и вывод полей UF_NAME, UF_XML_ID, UF_TAGS, UF_HEX_CODE.',
    'url' => '/local/sandbox/hlblock/pantone/',
   ],
   [
    'label' => 'PantoneColors: добавление',
    'description' => 'Создание нового цвета с названием, внешним кодом, тегами и HEX-кодом.',
    'url' => '/local/sandbox/hlblock/pantone/add/',
   ],
   [
    'label' => 'PantoneColors: редактирование',
    'description' => 'Обновление полей существующего элемента справочника цветов.',
    'url' => '/local/sandbox/hlblock/pantone/edit/',
   ],
   [
    'label' => 'PantoneColors: удаление',
    'description' => 'Удаление тестового элемента HL-блока и проверка результата.',
    'url' => '/local/sandbox/hlblock/pantone/delete/',
   ],
  ],
 ],

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
