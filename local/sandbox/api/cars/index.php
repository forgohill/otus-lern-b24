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


$iblock = CIBlock::GetList([], ['CODE' => 'car'])->Fetch();
$iblockId = (int)$iblock['ID'];


$iblockElementId = 30;
$iblockCode = 'car';

// OLD API
/* Получение активных элементов инфоблока методом CIBlockElement::GetList() **/
$lol = 'Получение активных элементов инфоблока методом CIBlockElement::GetList()';
$arFilter = [
 // 'IBLOCK_ID' => $iblockId, --- Вариант через ID ---
 'IBLOCK_CODE' => $iblockCode,
 'ACTIVE' => 'Y'
];
$arSelect = [
 'ID',
 'NAME',
 'CODE',
 'PROPERTY_MODEL',
 'PROPERTY_MANUFACTURER_ID',
 'PROPERTY_ENGINE_VOLUME',
 'PROPERTY_PRODUCTION_DATE',
 // 'PROPERTY_CITY_ID',
];
$res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
$items = [];
while ($arFields = $res->Fetch()) {
 $items[] = $arFields;
}

Log::addLog($items, false, 'cars_api_old');

/* Добавление элемента инфоблока методом CIBlockElement::Add() **/
$lol_2 = 'Добавление элемента инфоблока методом CIBlockElement::Add()';
$arElementProps = [
 'MODEL' => 'RAV4',
];
$arIblockFields = [
 'IBLOCK_ID' => $iblockId, /* --- Вариант через ID --- **/
 // 'IBLOCK_CODE' => $iblockCode, --- Не работает при добавлении элемента, только при получении ---
 'NAME' => 'Toyota RAV4',
 'CODE' => 'toyota_rav4',
 'PROPERTY_VALUES' => $arElementProps,
];
$objIblockElement = new \CIBlockElement();
$elementId = $objIblockElement->Add($arIblockFields);
if ($elementId) {
 $elementId;
} else {
 $elementId = 'Ошибка при добавлении элемента: ' . $objIblockElement->LAST_ERROR;
}

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
  <?php if ($iblockId): ?>
   <div style="padding:14px 18px; margin:16px 0; border-radius:10px; background:#f0fff4; color:#1f7a3d; border:1px solid #b7ebc6; font-family:Arial,sans-serif;">
    <b>Инфоблок найден</b><br>
    ID инфоблока 'car': <?= htmlspecialcharsbx($iblockId) ?>
   </div>
  <?php else: ?>
   <div style="padding:14px 18px; margin:16px 0; border-radius:10px; background:#fff5f5; color:#c53030; border:1px solid #f5b5b5; font-family:Arial,sans-serif;">
    <b>Ошибка</b><br>
    Инфоблок с кодом 'car' не найден
   </div>
  <?php endif; ?>
 </div>
</div>

<?
dump($lol, $items);
dump($lol_2, $elementId);
?>
<style>
 .sandbox-api-study {
  max-width: 1100px;
  margin: 24px auto 0;
  padding: 0 16px 40px;
 }

 .sandbox-api-study-section {
  background: #ffffff;
  border: 1px solid #dfe5ec;
  border-radius: 16px;
  overflow: hidden;
 }

 .sandbox-api-study-header {
  padding: 18px 24px;
  border-bottom: 1px solid #eef2f4;
  font-size: 20px;
  line-height: 28px;
  font-weight: 600;
  color: #1f2d3d;
  background: #fff;
 }

 .sandbox-api-study-body {
  padding: 24px;
  background: #fff;
 }

 .sandbox-api-study-intro {
  margin: 0 0 20px;
  font-size: 14px;
  line-height: 22px;
  color: #525c69;
 }

 .sandbox-api-study-card {
  border: 1px solid #eef2f4;
  border-radius: 14px;
  background: #fff;
  padding: 18px 18px 16px;
  margin-bottom: 16px;
 }

 .sandbox-api-study-card:last-child {
  margin-bottom: 0;
 }

 .sandbox-api-study-badge {
  display: inline-block;
  margin-bottom: 10px;
  padding: 4px 10px;
  border-radius: 999px;
  background: #eef2f4;
  color: #525c69;
  font-size: 12px;
  line-height: 18px;
  font-weight: 600;
 }

 .sandbox-api-study-title {
  margin: 0 0 10px;
  font-size: 17px;
  line-height: 24px;
  font-weight: 600;
  color: #2f3b47;
 }

 .sandbox-api-study-text {
  margin: 0 0 12px;
  font-size: 14px;
  line-height: 22px;
  color: #525c69;
 }

 .sandbox-api-study-code {
  margin: 0 0 12px;
  padding: 14px 16px;
  border: 1px solid #eef2f4;
  border-radius: 12px;
  background: #f8fafc;
  overflow-x: auto;
 }

 .sandbox-api-study-code code {
  display: block;
  white-space: pre;
  font-family: Consolas, Menlo, Monaco, monospace;
  font-size: 13px;
  line-height: 21px;
  color: #1f2d3d;
 }

 .sandbox-api-study-note {
  margin: 0;
  padding: 12px 14px;
  border-radius: 10px;
  background: #f6fcff;
  border: 1px solid #d7eef9;
  font-size: 13px;
  line-height: 21px;
  color: #4a5568;
 }

 @media (max-width: 768px) {
  .sandbox-api-study-header {
   font-size: 18px;
   line-height: 24px;
  }

  .sandbox-api-study-body {
   padding: 18px;
  }
 }
</style>

<div class="sandbox-api-study">
 <div class="sandbox-api-study-section">
  <div class="sandbox-api-study-header">Разбор кода в этом файле</div>

  <div class="sandbox-api-study-body">
   <p class="sandbox-api-study-intro">
    Ниже краткий разбор того, что именно изучается в этом файле: работа со старым API инфоблоков,
    получение списка элементов, логирование результата и добавление нового элемента в инфоблок <b>car</b>.
   </p>

   <div class="sandbox-api-study-card">
    <div class="sandbox-api-study-badge">Шаг 1</div>
    <h3 class="sandbox-api-study-title">Подключение модуля и поиск инфоблока</h3>

    <pre class="sandbox-api-study-code"><code>Loader::includeModule('iblock');

if (!Loader::includeModule('iblock')) {
    die('Модуль iblock не подключен');
}

$iblock = CIBlock::GetList([], ['CODE' => 'car'])->Fetch();
$iblockId = (int)$iblock['ID'];</code></pre>

    <p class="sandbox-api-study-text">
     Сначала подключается модуль <b>iblock</b>, без которого классы инфоблоков просто не будут работать.
     Затем через <b>CIBlock::GetList()</b> ищется инфоблок с кодом <b>car</b>, а его ID приводится к целому числу.
    </p>

    <p class="sandbox-api-study-note">
     Это подготовительный этап: дальше этот ID используется при выборке и при добавлении элемента.
    </p>
   </div>

   <div class="sandbox-api-study-card">
    <div class="sandbox-api-study-badge">Шаг 2</div>
    <h3 class="sandbox-api-study-title">Получение активных элементов через CIBlockElement::GetList()</h3>

    <pre class="sandbox-api-study-code"><code>$arFilter = [
    // 'IBLOCK_ID' => $iblockId,
    'IBLOCK_CODE' => $iblockCode,
    'ACTIVE' => 'Y'
];

$arSelect = [
    'ID',
    'NAME',
    'CODE',
    'PROPERTY_MODEL',
    'PROPERTY_MANUFACTURER_ID',
    'PROPERTY_ENGINE_VOLUME',
    'PROPERTY_PRODUCTION_DATE',
];

$res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

$items = [];
while ($arFields = $res->Fetch()) {
    $items[] = $arFields;
}</code></pre>

    <p class="sandbox-api-study-text">
     Здесь изучается классический способ чтения элементов инфоблока.
     В фильтре берутся только активные элементы, а в <b>select</b> перечисляются поля и свойства,
     которые нужно получить.
    </p>

    <p class="sandbox-api-study-text">
     Результат метода <b>CIBlockElement::GetList()</b> не приходит готовым массивом.
     Он возвращает объект результата, поэтому данные нужно забирать в цикле через <b>Fetch()</b>.
     Каждая итерация даёт одну строку, и она добавляется в массив <b>$items</b>.
    </p>

    <p class="sandbox-api-study-note">
     Это хороший учебный пример того, как старое API возвращает данные: не коллекциями объектов, а по одной записи за проход цикла.
    </p>
   </div>

   <div class="sandbox-api-study-card">
    <div class="sandbox-api-study-badge">Шаг 3</div>
    <h3 class="sandbox-api-study-title">Логирование результата выборки</h3>

    <pre class="sandbox-api-study-code"><code>Log::addLog($items, false, 'cars_api_old');</code></pre>

    <p class="sandbox-api-study-text">
     После получения списка машин массив <b>$items</b> дополнительно пишется в лог.
     Это уже не часть API инфоблоков, а отдельная учебная отладка внутри твоего проекта.
    </p>

    <p class="sandbox-api-study-note">
     Смысл блока простой: не только вывести результат в браузер через dump, но и сохранить его в лог-файл для проверки структуры данных.
    </p>
   </div>

   <div class="sandbox-api-study-card">
    <div class="sandbox-api-study-badge">Шаг 4</div>
    <h3 class="sandbox-api-study-title">Подготовка данных для добавления элемента</h3>

    <pre class="sandbox-api-study-code"><code>$arElementProps = [
    'MODEL' => 'RAV4',
];

$arIblockFields = [
    'IBLOCK_ID' => $iblockId,
    // 'IBLOCK_CODE' => $iblockCode,
    'NAME' => 'Toyota RAV4',
    'CODE' => 'toyota_rav4',
    'PROPERTY_VALUES' => $arElementProps,
];</code></pre>

    <p class="sandbox-api-study-text">
     Здесь собирается массив полей нового элемента.
     В <b>NAME</b> и <b>CODE</b> задаются обычные поля элемента,
     а в <b>PROPERTY_VALUES</b> передаются значения свойств инфоблока.
    </p>

    <p class="sandbox-api-study-text">
     В данном случае создаётся машина <b>Toyota RAV4</b>, а её свойству <b>MODEL</b> задаётся значение <b>RAV4</b>.
    </p>

    <p class="sandbox-api-study-note">
     Это важный момент: свойства при добавлении передаются не отдельно, а внутри массива <b>PROPERTY_VALUES</b>.
    </p>
   </div>

   <div class="sandbox-api-study-card">
    <div class="sandbox-api-study-badge">Шаг 5</div>
    <h3 class="sandbox-api-study-title">Добавление элемента через CIBlockElement::Add()</h3>

    <pre class="sandbox-api-study-code"><code>$objIblockElement = new \CIBlockElement();
$elementId = $objIblockElement->Add($arIblockFields);

if ($elementId) {
    $elementId;
} else {
    $elementId = 'Ошибка при добавлении элемента: ' . $objIblockElement->LAST_ERROR;
}</code></pre>

    <p class="sandbox-api-study-text">
     Здесь создаётся объект <b>CIBlockElement</b> и вызывается метод <b>Add()</b>.
     Если элемент успешно добавлен, в переменную <b>$elementId</b> попадёт ID нового элемента.
     Если нет — в неё записывается текст ошибки из <b>LAST_ERROR</b>.
    </p>

    <p class="sandbox-api-study-note">
     То есть переменная <b>$elementId</b> в этом файле используется и как успешный результат, и как сообщение об ошибке.
    </p>
   </div>

   <div class="sandbox-api-study-card">
    <div class="sandbox-api-study-badge">Шаг 6</div>
    <h3 class="sandbox-api-study-title">Почему в конце стоят dump(...)</h3>

    <pre class="sandbox-api-study-code"><code>dump($lol, $items);
dump($lol_2, $elementId);</code></pre>

    <p class="sandbox-api-study-text">
     Финальные <b>dump(...)</b> — это учебный вывод результата на экран.
     Первый показывает, что вернул запрос списка элементов,
     второй — чем закончилась попытка добавления нового элемента.
    </p>

    <p class="sandbox-api-study-note">
     По сути весь файл — это sandbox по старому API инфоблоков: сначала чтение, потом запись, потом визуальная проверка результата.
    </p>
   </div>

   <div class="sandbox-api-study-card">
    <div class="sandbox-api-study-badge">Итог</div>
    <h3 class="sandbox-api-study-title">Что именно ты изучаешь этим файлом</h3>

    <p class="sandbox-api-study-text">
     Этот файл показывает базовую цепочку работы со старым API Bitrix:
     найти инфоблок, получить элементы по фильтру, выбрать поля и свойства,
     сохранить результат в лог и добавить новый элемент с набором свойств.
    </p>

    <p class="sandbox-api-study-note">
     Если совсем коротко: это учебная песочница на тему <b>старый API инфоблоков в Bitrix</b>.
    </p>
   </div>
  </div>
 </div>
</div>
<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
?>