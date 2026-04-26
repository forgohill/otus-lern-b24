<?php

namespace Bitrix\Titanic;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;

/**
 * Class PassengerCabinTable
 * 
 * Fields:
 * <ul>
 * <li> PASSENGER_ID int mandatory
 * <li> CABIN_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Titanic
 **/

class PassengerCabinTable extends DataManager
{
 /**
  * Returns DB table name for entity.
  *
  * @return string
  */
 public static function getTableName()
 {
  return 'otus_titanic_passenger_cabin';
 }

 /**
  * Returns entity map definition.
  *
  * @return array
  */
 public static function getMap()
 {
  return [
   'PASSENGER_ID' => (new IntegerField(
    'PASSENGER_ID',
    []
   ))->configureTitle(Loc::getMessage('PASSENGER_CABIN_ENTITY_PASSENGER_ID_FIELD'))
    ->configurePrimary(true),
   'CABIN_ID' => (new IntegerField(
    'CABIN_ID',
    []
   ))->configureTitle(Loc::getMessage('PASSENGER_CABIN_ENTITY_CABIN_ID_FIELD'))
    ->configurePrimary(true),
  ];
 }
}
