<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->SetTitle('API - Cars');

use App\Debug\Log;

use Bitrix\Crm\Entity\Lead;
use Bitrix\Main\Loader;
use Bitrix\Iblock\Iblock;

Loader::includeModule('iblock');

if (!Loader::includeModule('iblock')) {
 die('Модуль iblock не подключен');
}

// $iblockId = 16;
// $iblockElementId = 30;


// // OLD API
// $arFilter = [
//  'IBLOCK_ID' => $iblockId,
//  'ACTIVE' => 'Y'
// ];
// $arSelect = [
//  'ID',
//  'NAME',
//  // 'PROPERTY_MODEL',
//  // 'PROPERTY_MANUFACTURER_ID',
//  // 'PROPERTY_ENGINE_VOLUME',
//  // 'PROPERTY_PRODUCTION_DATE',
//  // 'PROPERTY_CITY_ID',
// ];
// $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
// $items = [];
// while ($arFields = $res->Fetch()) {
//  $items[] = $arFields;
// }

// Log::addLog($items, false, 'cars_api_old');

// dump($items);
// print_r($items);

$arFilter = [
 'IBLOCK_ID' => 16,
 'ID' => 30,
 'SHOW_NEW' => 'Y',
];

$arSelect = [
 'ID',
 'IBLOCK_ID',
 'NAME',
 'ACTIVE',
 'WF_STATUS_ID',
 'WF_NEW',
];

$res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

$items = [];
while ($arFields = $res->Fetch()) {
 $items[] = $arFields;
}

echo '<pre>';
echo 'rows = ' . $res->SelectedRowsCount() . PHP_EOL;
print_r($items);
echo '</pre>';

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
  <a href="/local/sandbox/index.php" class="ui-btn ui-btn-light-border ui-btn-round">Назад</a>
 </div>

 <div class="sandbox-hero">
  <h1>API - Cars</h1>
  <p>Через взаимодействие с инфоблоками Cars.</p>
 </div>

 <div class="sandbox-content">
  <p>Здесь будут эксперименты с API для работы с инфоблоками, связанными с автомобилями.</p>
 </div>
</div>

<?
if (function_exists('dump')) {
 echo 'dump() доступна';
} else {
 echo 'dump() НЕ доступна';
};
dump($items);
?>
<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
?>