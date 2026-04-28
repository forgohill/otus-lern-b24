<?php

namespace Models\Titanic\Orm;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Query\Join;

use Models\Titanic\Orm\PassengersTable as Passengers;

Loc::loadMessages(__FILE__);

/**
 * ORM-сущность для таблицы билетов.
 *
 * Поля:
 * <ul>
 * <li> ID int, обязательное, первичный ключ
 * <li> TICKET_RAW string(100), обязательное
 * <li> TICKET_PREFIX string(50), необязательное
 * <li> TICKET_NUMBER string(50), необязательное
 * <li> PASSENGER_COUNT int, необязательное, по умолчанию 0
 * <li> FARE_TOTAL double, необязательное
 * </ul>
 *
 * Связи:
 * <ul>
 * <li> `PASSENGERS` - отношение один-ко-многим к пассажирам</li>
 * </ul>
 *
 * @package Models\Titanic\Orm
 **/

class TicketsTable extends DataManager
{
 /**
  * Возвращает имя таблицы базы данных для сущности.
  *
  * @return string
  */
 public static function getTableName()
 {
  return 'otus_titanic_tickets';
 }

 /**
  * Возвращает описание полей и связей сущности.
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

   (new OneToMany('PASSENGERS', Passengers::class, 'TICKET')),
  ];
 }

 /**
  * Возвращает валидаторы для поля TICKET_RAW.
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
  * Возвращает валидаторы для поля TICKET_PREFIX.
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
  * Возвращает валидаторы для поля TICKET_NUMBER.
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
