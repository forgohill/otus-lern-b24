<?php

namespace Models\Titanic\Orm;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;

Loc::loadMessages(__FILE__);

/**
 * ORM-сущность для таблицы связей между пассажирами и каютами.
 *
 * Поля:
 * <ul>
 * <li> PASSENGER_ID int, обязательное, входит в состав первичного ключа
 * <li> CABIN_ID int, обязательное, входит в состав первичного ключа
 * </ul>
 *
 * @package Models\Titanic\Orm
 **/

class PassengerCabinTable extends DataManager
{
  /**
   * Возвращает имя таблицы базы данных для сущности.
   *
   * @return string
   */
  public static function getTableName()
  {
    return 'otus_titanic_passenger_cabin';
  }

  /**
   * Возвращает описание полей сущности.
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
