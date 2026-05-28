<?php

use Bitrix\Main\Page\Asset;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

/** @var CMain $APPLICATION */
$APPLICATION->SetTitle('Компонент списка таблицы бызы данных');
Asset::getInstance()->addCss('/local/sandbox/style.css');

use Bitrix\Main\Type;
use Models\Titanic\Orm\PassengersTable as Passengers;

// $collection = Passengers::getList([
//  'select' => [
//   'ID',
//   'PASSENGER_EXTERNAL_ID',
//   'FULL_NAME',
//   'SEX',
//   'AGE',
//   'FARE',
//   'SURVIVED',
//   'CABIN_RAW',
//  ]
// ])->fetchCollection();

// if ($collection) {
//  foreach ($collection as $passenger) {
//   echo $passenger->getFullName() . " - Возраст - " . $passenger->getAge() . "<br/>";
//  };
// }; 

?>
<h1 class="lesson-components-title">Таблицы:</h1>
<?php
$APPLICATION->IncludeComponent(
 "otus:table.views",
 "list",
 array(
  "COMPONENT_TEMPLATE" => "list",
  "SHOW_CHECKBOXES" => "N",
  "NUM_PAGE" => "10"
 ),
 false
);
?>
<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
?>
