<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

/**
 * @global \CMain $APPLICATION
 */

$APPLICATION->SetTitle('CRM - Поиск реквизитов по ИНН');
\Bitrix\Main\Page\Asset::getInstance()->addCss('/local/sandbox/style.css');

use Bitrix\Crm\Controller\Requisite\Entity as RequisiteController;
use Bitrix\Main\Loader;

Loader::includeModule('crm');

$description_1 = 'Пример поиска реквизитов компании по ИНН через CRM RequisiteController без создания компании и без записи реквизитов в базу.';
$inn = '5252041879';
$presetId = 1; // Для компании обычно используется пресет юрлица; для ИП в примере был presetId = 2.

$requisiteSearchResult = RequisiteController::searchAction(
 $inn,
 ['presetId' => $presetId]
);

$items = $requisiteSearchResult['items'] ?? [];
$firstItem = $items[0] ?? [];
$fields = $firstItem['fields'] ?? [];
?>

<div class="sandbox-page">
 <div class="sandbox-top-actions">
  <a href="/local/sandbox/index.php" class="ui-btn ui-btn-light-border ui-btn-round">Назад</a>
 </div>

 <div class="sandbox-hero">
  <h1>CRM - Поиск реквизитов по ИНН</h1>
  <p>Проверка получения реквизитов компании без создания CRM-сущностей.</p>
 </div>

 <div class="sandbox-section">
  <div class="sandbox-section-header">Результат поиска</div>
  <div class="sandbox-section-body">
   <p class="sandbox-text">
    Данные ниже выводятся через dump() и не сохраняются в CRM.
   </p>
  </div>
 </div>
</div>

<?php
dump([
 'description' => $description_1,
 'inn' => $inn,
 'preset_id' => $presetId,
 'items_count' => count($items),
]);

dump([
 'first_item_fields' => $fields,
]);

dump([
 'raw_result' => $requisiteSearchResult,
]);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
?>