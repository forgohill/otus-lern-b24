<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->SetTitle('ORM - Cars');

use Bitrix\Main\Loader;
use Bitrix\Iblock\Iblock;


Loader::includeModule('iblock');

$iblockCode = 'car';
$iblockGet = CIBlock::GetList([], ['CODE' => $iblockCode])->Fetch();
$iblockId = (int)$iblockGet['ID'];

$arFilter = [
 'IBLOCK_ID' => $iblockId,
 'CODE' => 'audi_q7',
];
$arSelect = ['ID', 'NAME', 'CODE'];
$res = CIBlockElement::GetList([], $arFilter, false, ['nTopCount' => 1], $arSelect);
$iblockElementId = null;
if ($arElement = $res->Fetch()) {
 $iblockElementId = (int)$arElement['ID'];
}

/** Здесь будет результат Iblock::wakeUp($iblockId) | getEntityDataClass()::getByPrimary() для элемента с кодом audi_q7  */
$description = 'Здесь будет результат Iblock::wakeUp($iblockId) | getEntityDataClass()::getByPrimary() для элемента с кодом audi_q7';
$iblock = Iblock::wakeUp($iblockId);
$element = $iblock->getEntityDataClass()::getByPrimary(
 $iblockElementId,
 ['select' => ['NAME', 'MODEL']]
)->fetchObject();

if ($element) {
 $nameElement = $element->get('NAME');
 $modelElement = $element->get('MODEL')->getValue();
}

/** Метод fetchCollection() преобразует результат getList() в коллекцию объектов, после чего в цикле формируется массив $result со значениями NAME и MODEL каждого элемента.*/
$description_2 = 'Метод fetchCollection() преобразует результат getList() в коллекцию объектов, после чего в цикле формируется массив $result со значениями NAME и MODEL каждого элемента.';
$elements = \Bitrix\Iblock\Elements\ElementCarTable::getList(['select' => ['NAME', 'MODEL']])->fetchCollection();
if ($elements) {
 foreach ($elements as $element) {
  $result[] = [
   'NAME' => $element->get('NAME'),
   'MODEL' => $element->getModel()?->getValue(),
  ];
 }
}

/** Метод fetchAll() возвращает результат getList() как массив записей, где каждая запись представляет собой массив со всеми выбранными полями элемента. */
$description_3 = 'Метод fetchAll() возвращает результат getList() как массив записей, где каждая запись представляет собой массив со всеми выбранными полями элемента.';
$carsFetchAll = \Bitrix\Iblock\Elements\ElementCarTable::getList(['select' => ['*']])->fetchAll();
if ($carsFetchAll) {
 foreach ($carsFetchAll as $carItemAll) {
  $carsResultAll[] = $carItemAll;
 }
}

/** Метод query() формирует ORM-запрос к элементам инфоблока, после чего fetchCollection() возвращает коллекцию объектов, у которых можно читать значения полей и свойств, изменять их через set...() и сохранять через save(). */
$description_4 = 'Метод query() формирует ORM-запрос к элементам инфоблока, после чего fetchCollection() возвращает коллекцию объектов, у которых можно читать значения полей и свойств, изменять их через set...() и сохранять через save().';
$carsQuery = \Bitrix\Iblock\Elements\ElementCarTable::query()
 ->addSelect('NAME')
 ->addSelect('MODEL')
 ->addSelect('ID')
 ->fetchCollection();
$carItems = [];
$carItemsStroke = [];

if ($carsQuery) {
 foreach ($carsQuery as $key => $carItem) {

  $value = $carItem->getModel()->getValue();

  if ($value == 'Q7') {
   $carItem->setModel('Q7 TEST');
   $carItem->save();
  }

  $carItems[] = [
   'ID' => $carItem->get('ID'),
   'NAME' => $carItem->get('NAME'),
   'MODEL' => $carItem->get('MODEL')->getValue(),
  ];

  $value = $carItem->getModel()->getValue();

  if ($value == 'Q7 TEST') {
   $carItem->setModel('Q7');
   $carItem->save();
  }
  $carItemsStroke[] = $carItem->get('NAME') . ' ' . $carItem->get('MODEL')->getValue();
 }
}

/** Метод PropertyTable::getList() получает список свойств заданного инфоблока по его ID, а метод fetch() последовательно возвращает каждое свойство в виде массива со всеми его полями. */
$description_5 = 'Метод PropertyTable::getList() получает список свойств заданного инфоблока по его ID, а метод fetch() последовательно возвращает каждое свойство в виде массива со всеми его полями.';
$dbIblockProps = \Bitrix\Iblock\PropertyTable::getList(array(
 'select' => array('*'),
 'filter' => array('IBLOCK_ID' => $iblockId)
));
$arIblockPropsArray = [];
$arIblockPropsStroke = [];

while ($arIblockProps = $dbIblockProps->fetch()) {
 $arIblockPropsArray[] = $arIblockProps;
 $arIblockPropsStroke[] = $arIblockProps['NAME'] . ' (' . $arIblockProps['CODE'] . ')';
}

/** Метод ElementTable::getList() получает список элементов инфоблока с основными полями, после чего для каждого элемента отдельно через CIBlockElement::GetProperty() загружаются все его свойства и добавляются в результирующий массив. */
$description_6 = 'Метод ElementTable::getList() получает список элементов инфоблока с основными полями, после чего для каждого элемента отдельно через CIBlockElement::GetProperty() загружаются все его свойства и добавляются в результирующий массив.';
$dbItems = \Bitrix\Iblock\ElementTable::getList(array(
 'select' => array('ID', 'NAME', 'IBLOCK_ID'),
 'filter' => array('IBLOCK_ID' => $iblockId)
));

$elementsWithProperties = [];
while ($arItem = $dbItems->fetch()) {
 $dbProperty = \CIBlockElement::getProperty(
  $arItem['IBLOCK_ID'],
  $arItem['ID']
 );
 while ($arProperty = $dbProperty->Fetch()) {
  $arItem['PROPERTIES'][] = $arProperty;
 }
 $elementsWithProperties[] = $arItem;
}

/** Метод ElementCarTable::add() создает новый элемент инфоблока через ORM, после чего при успешном добавлении через CIBlockElement::SetPropertyValuesEx() элементу задаются значения его свойств. */
$description_7 = 'Метод ElementCarTable::add() создает новый элемент инфоблока через ORM, после чего при успешном добавлении через CIBlockElement::SetPropertyValuesEx() элементу задаются значения его свойств.';

$cityIblockCode = 'city';
$cityIblock = CIBlock::GetList([], ['CODE' => $cityIblockCode])->Fetch();
$cityIblockId = (int)$cityIblock['ID'];

$cityElement = CIBlockElement::GetList(
 [],
 [
  'IBLOCK_ID' => $cityIblockId,
  'CODE' => 'berlin',
 ],
 false,
 ['nTopCount' => 1],
 ['ID', 'NAME', 'CODE']
)->Fetch();

$cityElementId = $cityElement ? (int)$cityElement['ID'] : null;

$manufacturerIblockCode = 'manufacturer';
$manufacturerIblock = CIBlock::GetList([], ['CODE' => $manufacturerIblockCode])->Fetch();
$manufacturerIblockId = (int)$manufacturerIblock['ID'];

$manufacturerElement = CIBlockElement::GetList(
 [],
 [
  'IBLOCK_ID' => $manufacturerIblockId,
  'CODE' => 'bmw',
 ],
 false,
 ['nTopCount' => 1],
 ['ID', 'NAME', 'CODE']
)->Fetch();

$manufacturerElementId = $manufacturerElement ? (int)$manufacturerElement['ID'] : null;


$resultAddCarTable = \Bitrix\Iblock\Elements\ElementCarTable::add(array(
 'NAME' => 'TEST',
 'ACTIVE' => 'Y',
));
$idForResDelete = null;
if ($resultAddCarTable->isSuccess()) {
 $id = $resultAddCarTable->getId();
 $idForResDelete = $id;
 CIBlockElement::SetPropertyValuesEx($id, false, array(
  'MODEL' => 'X5',
  'MANUFACTURER_ID' => $manufacturerElementId,
  'CITY_ID' => $cityElementId,
  'ENGINE_VOLUME' => '4',
  'PRODUCTION_DATE' => date('d.m.Y'),
 ));
} else {
 dump($resultAddCarTable->getErrorMessages());
}

/** Блок через Iblock::wakeUp() и getEntityDataClass()::getByPrimary() получает только что созданный элемент по ID, собирает снимок его данных перед удалением, отдельно обрабатывает множественное свойство CITY_ID: из ORM-коллекции берутся ID городов, затем через CIBlockElement::GetList() подтягиваются их названия, после чего формируется массив $elementBeforeDelete и выполняется удаление элемента. */
$description_8 = 'Блок через Iblock::wakeUp() и getEntityDataClass()::getByPrimary() получает только что созданный элемент по ID, собирает снимок его данных перед удалением, отдельно обрабатывает множественное свойство CITY_ID: из ORM-коллекции берутся ID городов, затем через CIBlockElement::GetList() подтягиваются их названия, после чего формируется массив $elementBeforeDelete и выполняется удаление элемента.';
$elementBeforeDelete = [];
$cityIds = [];
$cityNames = [];

$iblockForDelete = Iblock::wakeUp($iblockId);

if ($iblockForDelete) {
 $element = $iblockForDelete->getEntityDataClass()::getByPrimary(
  $idForResDelete,
  [
   'select' => [
    'ID',
    'NAME',
    'MODEL',
    'MANUFACTURER_ID',
    'CITY_ID',
    'ENGINE_VOLUME',
    'PRODUCTION_DATE',
   ]
  ]
 )->fetchObject();

 if ($element) {
  foreach ($element->get('CITY_ID') as $cityProperty) {
   $cityId = (int)$cityProperty->getValue();
   if ($cityId > 0) {
    $cityIds[] = $cityId;
   }
  }

  if ($cityIds) {
   $cityRes = CIBlockElement::GetList(
    [],
    ['ID' => array_unique($cityIds)],
    false,
    false,
    ['ID', 'NAME']
   );

   while ($cityRow = $cityRes->Fetch()) {
    $cityNames[(int)$cityRow['ID']] = $cityRow['NAME'];
   }
  }

  $cities = [];
  foreach ($cityIds as $cityId) {
   $cities[] = [
    'ID' => $cityId,
    'NAME' => $cityNames[$cityId] ?? null,
   ];
  }

  $elementBeforeDelete = [
   'ID' => $element->get('ID'),
   'NAME' => $element->get('NAME'),
   'MODEL' => $element->get('MODEL')?->getValue(),
   'MANUFACTURER_ID' => $element->get('MANUFACTURER_ID')?->getValue(),
   'CITY_ID' => $cities,
   'ENGINE_VOLUME' => $element->get('ENGINE_VOLUME')?->getValue(),
   'PRODUCTION_DATE' => $element->get('PRODUCTION_DATE')?->getValue(),
  ];
 }
}
$resDelete = \Bitrix\Iblock\Elements\ElementCarTable::delete($idForResDelete);

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
  <h1>ORM - Cars</h1>
  <p>Через взаимодействие с инфоблоками Cars.</p>
 </div>

 <div class="sandbox-content">
  <p>Здесь будут эксперименты с ORM для работы с инфоблоками, связанными с автомобилями.</p>

  <?php if ($iblockId): ?>
   <div style="max-width: 30%; padding:14px 18px; margin:16px 0; border-radius:10px; background:#f0fff4; color:#1f7a3d; border:1px solid #b7ebc6; font-family:Arial,sans-serif;">
    <b>Инфоблок найден</b><br>
    ID инфоблока 'car': <?= htmlspecialcharsbx($iblockId) ?>
   </div>
  <?php else: ?>
   <div style="max-width: 30%; padding:14px 18px; margin:16px 0; border-radius:10px; background:#fff5f5; color:#c53030; border:1px solid #f5b5b5; font-family:Arial,sans-serif;">
    <b>Ошибка</b><br>
    Инфоблок с кодом 'car' не найден
   </div>
  <?php endif; ?>

  <?php if ($iblockElementId): ?>
   <div style="max-width: 30%; margin:12px 0;padding:10px 14px;border-radius:8px;font:14px/1.4 Arial,sans-serif;background:#f0fff4;color:#1f7a3d;border:1px solid #b7ebc6;">
    <b>ID элемента audi_q7:</b> <?= htmlspecialcharsbx($iblockElementId) ?>
   </div>
  <?php else: ?>
   <div style="max-width: 30%; margin:12px 0;padding:10px 14px;border-radius:8px;font:14px/1.4 Arial,sans-serif;background:#fff5f5;color:#c53030;border:1px solid #f5b5b5;">
    <b>Ошибка:</b> Элемент с кодом audi_q7 не найден
   </div>
  <?php endif; ?>

 </div>
</div>

<?

dump([
 'description' => $description,
 'NAME' => $nameElement,
 'MODEL' => $modelElement,
]);

dump([
 'description_2' => $description_2,
 'Result' => $result,
]);

dump([
 'description_3' => $description_3,
 'carsResultAll' => $carsResultAll,
]);

dump([
 'description_4' => $description_4,
 'carItems' => $carItems,
 'carItemsStroke' => $carItemsStroke,
]);

dump([
 'description_5' => $description_5,
 'arIblockPropsArray' => $arIblockPropsArray,
 'arIblockPropsStroke' => $arIblockPropsStroke,
]);

dump([
 'description_6' => $description_6,
 'elementsWithProperties' => $elementsWithProperties,
]);
dump([
 'description_7' => $description_7,
 'resultAddCarTable' => $resultAddCarTableLog = $resultAddCarTable->isSuccess() ? $resultAddCarTable->getId() : $resultAddCarTable->getErrorMessages(),
]);

if ($resDelete->isSuccess()) {
 dump([
  'description_8' => $description_8,
  'id' => $idForResDelete,
  'message' => 'Удаление прошло успешно',
  'elementBeforeDelete' => $elementBeforeDelete,
 ]);
} else {
 dump([
  'id' => $idForResDelete,
  'errors' => $resDelete->getErrorMessages(),
 ]);
}

?>

<style>
 .sandbox-study {
  max-width: 1100px;
  margin: 24px auto 0;
  padding: 0 16px 40px;
 }

 .sandbox-study-section {
  background: #fff;
  border: 1px solid #dfe5ec;
  border-radius: 16px;
  overflow: hidden;
 }

 .sandbox-study-header {
  padding: 18px 24px;
  border-bottom: 1px solid #eef2f4;
  font-size: 20px;
  line-height: 28px;
  font-weight: 600;
  color: #1f2d3d;
  background: #fff;
 }

 .sandbox-study-body {
  padding: 24px;
  background: #fff;
 }

 .sandbox-study-intro {
  margin: 0 0 20px;
  font-size: 14px;
  line-height: 22px;
  color: #525c69;
 }

 .sandbox-study-card {
  border: 1px solid #eef2f4;
  border-radius: 14px;
  background: #fff;
  padding: 18px 18px 16px;
  margin-bottom: 16px;
 }

 .sandbox-study-card:last-child {
  margin-bottom: 0;
 }

 .sandbox-study-badge {
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

 .sandbox-study-title {
  margin: 0 0 10px;
  font-size: 17px;
  line-height: 24px;
  font-weight: 600;
  color: #2f3b47;
 }

 .sandbox-study-text {
  margin: 0 0 12px;
  font-size: 14px;
  line-height: 22px;
  color: #525c69;
 }

 .sandbox-study-code {
  margin: 0 0 12px;
  padding: 14px 16px;
  border: 1px solid #eef2f4;
  border-radius: 12px;
  background: #f8fafc;
  overflow-x: auto;
 }

 .sandbox-study-code code {
  font-family: Consolas, Menlo, Monaco, monospace;
  font-size: 13px;
  line-height: 21px;
  color: #1f2d3d;
  white-space: pre;
  display: block;
 }

 .sandbox-study-note {
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
  .sandbox-study-header {
   font-size: 18px;
   line-height: 24px;
  }

  .sandbox-study-body {
   padding: 18px;
  }
 }
</style>

<div class="sandbox-study">
 <div class="sandbox-study-section">
  <div class="sandbox-study-header">Разбор кода в этом файле</div>

  <div class="sandbox-study-body">
   <p class="sandbox-study-intro">
    Ниже краткий разбор того, что именно изучается в этом sandbox-файле и как работает каждый основной блок кода.
   </p>

   <div class="sandbox-study-card">
    <div class="sandbox-study-badge">Шаг 1</div>
    <h3 class="sandbox-study-title">Поиск инфоблока и конкретного элемента</h3>

    <pre class="sandbox-study-code"><code>$iblockCode = 'car';
      $iblockGet = CIBlock::GetList([], ['CODE' => $iblockCode])->Fetch();
      $iblockId = (int)$iblockGet['ID'];

      $arFilter = [
          'IBLOCK_ID' => $iblockId,
          'CODE' => 'audi_q7',
      ];
      $res = CIBlockElement::GetList([], $arFilter, false, ['nTopCount' => 1], ['ID', 'NAME', 'CODE']);
      if ($arElement = $res->Fetch()) {
          $iblockElementId = (int)$arElement['ID'];
      }</code></pre>

    <p class="sandbox-study-text">
     Сначала код находит сам инфоблок по символьному коду <b>car</b>, а потом ищет внутри него один конкретный элемент по коду <b>audi_q7</b>.
     Это подготовительный этап: без ID инфоблока и ID элемента дальше нельзя удобно показывать ORM-примеры.
    </p>

    <p class="sandbox-study-note">
     По сути это мост между старым API инфоблоков и дальнейшими ORM-экспериментами.
    </p>
   </div>

   <div class="sandbox-study-card">
    <div class="sandbox-study-badge">Шаг 2</div>
    <h3 class="sandbox-study-title">Получение одного ORM-объекта через wakeUp() и getByPrimary()</h3>

    <pre class="sandbox-study-code"><code>$iblock = Iblock::wakeUp($iblockId);

       $element = $iblock->getEntityDataClass()::getByPrimary(
           $iblockElementId,
           ['select' => ['NAME', 'MODEL']]
       )->fetchObject();

       if ($element) {
           $nameElement = $element->get('NAME');
           $modelElement = $element->get('MODEL')->getValue();
       }</code></pre>

    <p class="sandbox-study-text">
     Здесь уже начинается D7 ORM. Метод <b>wakeUp()</b> поднимает ORM-описание инфоблока, после чего через
     <b>getEntityDataClass()</b> получаем класс сущности, а <b>getByPrimary()</b> выбирает один элемент по первичному ключу, то есть по ID.
    </p>

    <p class="sandbox-study-text">
     Дальше результат превращается в объект через <b>fetchObject()</b>, и из него можно читать поля и свойства:
     обычное поле через <b>get('NAME')</b>, а свойство типа ORM-объекта через <b>get('MODEL')->getValue()</b>.
    </p>

    <p class="sandbox-study-note">
     Это пример, когда нужен не список, а один конкретный объект для точечной работы.
    </p>
   </div>

   <div class="sandbox-study-card">
    <div class="sandbox-study-badge">Шаг 3</div>
    <h3 class="sandbox-study-title">fetchCollection(): список как коллекция объектов</h3>

    <pre class="sandbox-study-code"><code>$elements = \Bitrix\Iblock\Elements\ElementCarTable::getList([
            'select' => ['NAME', 'MODEL']
        ])->fetchCollection();

        if ($elements) {
            foreach ($elements as $element) {
                $result[] = [
                    'NAME' => $element->get('NAME'),
                    'MODEL' => $element->getModel()?->getValue(),
                ];
            }
        }</code></pre>

    <p class="sandbox-study-text">
     В этом блоке получаем уже не один элемент, а набор элементов. Но важно, что
     <b>fetchCollection()</b> возвращает не обычный массив, а коллекцию ORM-объектов.
    </p>

    <p class="sandbox-study-text">
     Поэтому внутри цикла можно вызывать объектные методы:
     <b>get('NAME')</b>, <b>getModel()</b>, <b>getValue()</b>.
     После этого ты сам собираешь удобный массив <b>$result</b>, который уже и отдаёшь в <b>dump()</b>.
    </p>

    <p class="sandbox-study-note">
     Это удобно, когда тебе нужны преимущества ORM, но вывод в итоге хочется увидеть как простой массив.
    </p>
   </div>

   <div class="sandbox-study-card">
    <div class="sandbox-study-badge">Шаг 4</div>
    <h3 class="sandbox-study-title">fetchAll(): тот же список, но сразу массивом</h3>

    <pre class="sandbox-study-code"><code>$carsFetchAll = \Bitrix\Iblock\Elements\ElementCarTable::getList([
           'select' => ['*']
       ])->fetchAll();

       if ($carsFetchAll) {
           foreach ($carsFetchAll as $carItemAll) {
               $carsResultAll[] = $carItemAll;
           }
       }</code></pre>

    <p class="sandbox-study-text">
     Здесь запрос похожий, но результат забирается через <b>fetchAll()</b>.
     Это уже не коллекция ORM-объектов, а сразу обычный массив записей.
    </p>

    <p class="sandbox-study-text">
     Такой вариант удобен, когда не нужно вызывать методы объекта и ты просто хочешь быстро посмотреть,
     какие данные реально пришли из запроса в плоском виде.
    </p>

    <p class="sandbox-study-note">
     Если коротко: <b>fetchCollection()</b> — “объекты”, <b>fetchAll()</b> — “массивы”.
    </p>
   </div>

   <div class="sandbox-study-card">
    <div class="sandbox-study-badge">Шаг 5</div>
    <h3 class="sandbox-study-title">query(): ручная сборка ORM-запроса и изменение свойства</h3>

    <pre class="sandbox-study-code"><code>$carsQuery = \Bitrix\Iblock\Elements\ElementCarTable::query()
    ->addSelect('NAME')
    ->addSelect('MODEL')
    ->addSelect('ID')
    ->fetchCollection();

          foreach ($carsQuery as $carItem) {
              $value = $carItem->getModel()->getValue();

              if ($value == 'Q7') {
                  $carItem->setModel('Q7 TEST');
                  $carItem->save();
              }

              $carItems[] = [
                  'ID' => $carItem->get('ID'),
                  'NAME' => $carItem->get('NAME'),
                  'MODEL' => $carItem->get('MODEL')->getValue(),
              ];

              if ($carItem->getModel()->getValue() == 'Q7 TEST') {
                  $carItem->setModel('Q7');
                  $carItem->save();
              }
               }</code></pre>

    <p class="sandbox-study-text">
     Это уже более “боевой” ORM-подход. Через <b>query()</b> ты сам собираешь запрос по частям:
     какие поля выбирать, в каком виде получать результат, как обходить коллекцию.
    </p>

    <p class="sandbox-study-text">
     Главная идея этого блока — показать, что ORM-объект можно не только читать, но и менять:
     берётся значение свойства <b>MODEL</b>, при совпадении вызывается <b>setModel(...)</b>, затем <b>save()</b>.
    </p>

    <p class="sandbox-study-text">
     У тебя здесь ещё и учебный приём: значение временно меняется на <b>Q7 TEST</b>, потом после формирования результата возвращается обратно на <b>Q7</b>.
     То есть код демонстрирует сам механизм изменения, но не оставляет постоянную модификацию данных.
    </p>

    <p class="sandbox-study-note">
     Это хороший учебный пример на тему: “ORM умеет не только читать, но и сохранять изменения”.
    </p>
   </div>

   <div class="sandbox-study-card">
    <div class="sandbox-study-badge">Шаг 6</div>
    <h3 class="sandbox-study-title">PropertyTable: получить структуру свойств инфоблока</h3>

    <pre class="sandbox-study-code"><code>$dbIblockProps = \Bitrix\Iblock\PropertyTable::getList([
              'select' => ['*'],
              'filter' => ['IBLOCK_ID' => $iblockId]
          ]);

          while ($arIblockProps = $dbIblockProps->fetch()) {
              $arIblockPropsArray[] = $arIblockProps;
              $arIblockPropsStroke[] = $arIblockProps['NAME'] . ' (' . $arIblockProps['CODE'] . ')';
          }</code></pre>

    <p class="sandbox-study-text">
     Этот блок работает уже не с элементами, а со <b>списком свойств самого инфоблока</b>.
     То есть здесь ты изучаешь не значения у конкретной машины, а структуру: какие вообще свойства существуют у инфоблока <b>car</b>.
    </p>

    <p class="sandbox-study-note">
     Это полезно, когда нужно понять схему инфоблока: какие поля-свойства доступны, как они называются и какие у них коды.
    </p>
   </div>

   <div class="sandbox-study-card">
    <div class="sandbox-study-badge">Шаг 7</div>
    <h3 class="sandbox-study-title">ElementTable + CIBlockElement::GetProperty(): элементы и их свойства</h3>

    <pre class="sandbox-study-code"><code>$dbItems = \Bitrix\Iblock\ElementTable::getList([
           'select' => ['ID', 'NAME', 'IBLOCK_ID'],
           'filter' => ['IBLOCK_ID' => $iblockId]
       ]);    

       while ($arItem = $dbItems->fetch()) {
           $dbProperty = \CIBlockElement::GetProperty(
               $arItem['IBLOCK_ID'],
               $arItem['ID']
           );

           while ($arProperty = $dbProperty->Fetch()) {
               $arItem['PROPERTIES'][] = $arProperty;
           }

           $elementsWithProperties[] = $arItem;
       }</code></pre>

    <p class="sandbox-study-text">
     Здесь показан смешанный подход. Сначала через <b>ElementTable</b> получаешь основные поля элементов,
     а потом для каждого элемента отдельно подтягиваешь его свойства через старый API
     <b>CIBlockElement::GetProperty()</b>.
    </p>

    <p class="sandbox-study-text">
     В итоге собирается массив, где у каждого элемента есть и базовые поля, и вложенный массив <b>PROPERTIES</b>.
     Это удобно для изучения структуры данных и для сравнения, как сочетаются D7 и старый API.
    </p>

    <p class="sandbox-study-note">
     Такой вариант часто используют, когда базовые поля удобно брать через ORM, а свойства проще и привычнее дочитать классическим способом.
    </p>
   </div>

   <div class="sandbox-study-card">
    <div class="sandbox-study-badge">Шаг 8</div>
    <h3 class="sandbox-study-title">ElementCarTable::add(): создание элемента и заполнение его свойств</h3>

    <pre class="sandbox-study-code"><code>$resultAddCarTable = \Bitrix\Iblock\Elements\ElementCarTable::add([
            'NAME' => 'TEST',
            'ACTIVE' => 'Y',
        ]);

        if ($resultAddCarTable->isSuccess()) {
            $id = $resultAddCarTable->getId();

         CIBlockElement::SetPropertyValuesEx($id, false, [
        'MODEL' => 'X5',
        'MANUFACTURER_ID' => $manufacturerElementId,
        'CITY_ID' => $cityElementId,
        'ENGINE_VOLUME' => '4',
               'PRODUCTION_DATE' => date('d.m.Y'),
           ]);
       } else {
           dump($resultAddCarTable->getErrorMessages());
       }</code></pre>

    <p class="sandbox-study-text">
     Здесь код сначала создаёт новый элемент инфоблока <b>car</b> через ORM-метод <b>ElementCarTable::add()</b>.
     На этом шаге задаются только базовые поля элемента, например <b>NAME</b> и <b>ACTIVE</b>.
    </p>

    <p class="sandbox-study-text">
     Если добавление прошло успешно, из результата берётся ID нового элемента через <b>getId()</b>.
     Затем этому элементу отдельным вызовом задаются свойства через
     <b>CIBlockElement::SetPropertyValuesEx()</b>: модель, производитель, город, объём двигателя и дата выхода.
    </p>

    <p class="sandbox-study-note">
     Смысл шага в том, что элемент создаётся через ORM, а значения свойств после этого удобно дозаполняются старым API.
     Это учебный пример смешанного подхода: D7 ORM для создания записи + классический API Bitrix для свойств.
    </p>
   </div>

   <div class="sandbox-study-card">
    <div class="sandbox-study-badge">Шаг 9</div>
    <h3 class="sandbox-study-title">Получение данных перед удалением и удаление элемента</h3>

    <pre class="sandbox-study-code"><code>$elementBeforeDelete = [];
        $cityIds = [];
        $cityNames = [];

        $iblockForDelete = Iblock::wakeUp($iblockId);

        if ($iblockForDelete) {
            $element = $iblockForDelete->getEntityDataClass()::getByPrimary(
        $idForResDelete,
        [
            'select' => [
                'ID',
                'NAME',
                'MODEL',
                'MANUFACTURER_ID',
                'CITY_ID',
                'ENGINE_VOLUME',
                'PRODUCTION_DATE',
            ]
        ]
    )->fetchObject();

    if ($element) {
        foreach ($element->get('CITY_ID') as $cityProperty) {
            $cityId = (int)$cityProperty->getValue();
            if ($cityId > 0) {
                $cityIds[] = $cityId;
            }
        }

        if ($cityIds) {
            $cityRes = CIBlockElement::GetList(
                [],
                ['ID' => array_unique($cityIds)],
                false,
                false,
                ['ID', 'NAME']
            );

            while ($cityRow = $cityRes->Fetch()) {
                $cityNames[(int)$cityRow['ID']] = $cityRow['NAME'];
            }
        }

        $cities = [];
        foreach ($cityIds as $cityId) {
            $cities[] = [
                'ID' => $cityId,
                'NAME' => $cityNames[$cityId] ?? null,
            ];
        }

        $elementBeforeDelete = [
            'ID' => $element->get('ID'),
            'NAME' => $element->get('NAME'),
            'MODEL' => $element->get('MODEL')?->getValue(),
            'MANUFACTURER_ID' => $element->get('MANUFACTURER_ID')?->getValue(),
            'CITY_ID' => $cities,
            'ENGINE_VOLUME' => $element->get('ENGINE_VOLUME')?->getValue(),
            'PRODUCTION_DATE' => $element->get('PRODUCTION_DATE')?->getValue(),
        ];
    }
       }

       $resDelete = \Bitrix\Iblock\Elements\ElementCarTable::delete($idForResDelete);</code></pre>

    <p class="sandbox-study-text">
     Этот блок нужен для того, чтобы перед удалением не потерять данные о только что созданном элементе.
     Сначала код снова поднимает ORM-сущность инфоблока через <b>Iblock::wakeUp()</b> и получает элемент по его ID через
     <b>getByPrimary(...)->fetchObject()</b>.
    </p>

    <p class="sandbox-study-text">
     Дальше из объекта читаются поля и свойства элемента. Особенность здесь в том, что
     <b>CITY_ID</b> — множественное свойство, поэтому код проходит по нему циклом,
     собирает ID всех городов, а затем отдельным запросом получает их названия и
     формирует удобный массив вида <b>ID + NAME</b>.
    </p>

    <p class="sandbox-study-text">
     После этого собирается массив <b>$elementBeforeDelete</b> — это снимок данных элемента перед удалением.
     Уже затем вызывается удаление по ID, а в <b>dump()</b> можно вывести и результат удаления, и данные элемента,
     который был удалён.
    </p>

    <p class="sandbox-study-note">
     Главная идея шага: сначала снять данные элемента перед удалением, потом удалить запись.
     Так ты не удаляешь элемент “вслепую”, а видишь, что именно было создано и что именно потом удалилось.
    </p>
   </div>
  </div>
 </div>
</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
?>