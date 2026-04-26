<?php

namespace Models\Titanic\Orm;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

Loc::loadMessages(__FILE__);

/**
 * Class TicketsTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TICKET_RAW string(100) mandatory
 * <li> TICKET_PREFIX string(50) optional
 * <li> TICKET_NUMBER string(50) optional
 * <li> PASSENGER_COUNT int optional default 0
 * <li> FARE_TOTAL double optional
 * </ul>
 *
 * @package Bitrix\Titanic
 **/

class TicketsTable extends DataManager
{
 /**
  * Returns DB table name for entity.
  *
  * @return string
  */
 public static function getTableName()
 {
  return 'otus_titanic_tickets';
 }

 /**
  * Returns entity map definition.
  *
  * @return array
  */
 public static function getMap()
 {
  return [
   'ID' => (new IntegerField(
    'ID',
    []
   ))->configureTitle(Loc::getMessage('TICKETS_ENTITY_ID_FIELD'))
    ->configurePrimary(true)
    ->configureAutocomplete(true),
   'TICKET_RAW' => (new StringField(
    'TICKET_RAW',
    [
     'validation' => [__CLASS__, 'validateTicketRaw']
    ]
   ))->configureTitle(Loc::getMessage('TICKETS_ENTITY_TICKET_RAW_FIELD'))
    ->configureRequired(true),
   'TICKET_PREFIX' => (new StringField(
    'TICKET_PREFIX',
    [
     'validation' => [__CLASS__, 'validateTicketPrefix']
    ]
   ))->configureTitle(Loc::getMessage('TICKETS_ENTITY_TICKET_PREFIX_FIELD')),
   'TICKET_NUMBER' => (new StringField(
    'TICKET_NUMBER',
    [
     'validation' => [__CLASS__, 'validateTicketNumber']
    ]
   ))->configureTitle(Loc::getMessage('TICKETS_ENTITY_TICKET_NUMBER_FIELD')),
   'PASSENGER_COUNT' => (new IntegerField(
    'PASSENGER_COUNT',
    []
   ))->configureTitle(Loc::getMessage('TICKETS_ENTITY_PASSENGER_COUNT_FIELD'))
    ->configureDefaultValue(0),
   'FARE_TOTAL' => (new FloatField(
    'FARE_TOTAL',
    []
   ))->configureTitle(Loc::getMessage('TICKETS_ENTITY_FARE_TOTAL_FIELD')),
  ];
 }

 /**
  * Returns validators for TICKET_RAW field.
  *
  * @return array
  */
 public static function validateTicketRaw(): array
 {
  return [
   new LengthValidator(null, 100),
  ];
 }

 /**
  * Returns validators for TICKET_PREFIX field.
  *
  * @return array
  */
 public static function validateTicketPrefix(): array
 {
  return [
   new LengthValidator(null, 50),
  ];
 }

 /**
  * Returns validators for TICKET_NUMBER field.
  *
  * @return array
  */
 public static function validateTicketNumber(): array
 {
  return [
   new LengthValidator(null, 50),
  ];
 }
}
