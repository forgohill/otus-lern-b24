<?php

namespace Models\Titanic\Orm;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

Loc::loadMessages(__FILE__);

/**
 * Class CabinsTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CABIN_CODE string(50) mandatory
 * <li> DECK_CODE string(10) mandatory
 * <li> DECK_ELEMENT_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Titanic
 **/

class CabinsTable extends DataManager
{
  /**
   * Returns DB table name for entity.
   *
   * @return string
   */
  public static function getTableName()
  {
    return 'otus_titanic_cabins';
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
      ))->configureTitle(Loc::getMessage('CABINS_ENTITY_ID_FIELD'))
        ->configurePrimary(true)
        ->configureAutocomplete(true),
      'CABIN_CODE' => (new StringField(
        'CABIN_CODE',
        [
          'validation' => [__CLASS__, 'validateCabinCode']
        ]
      ))->configureTitle(Loc::getMessage('CABINS_ENTITY_CABIN_CODE_FIELD'))
        ->configureRequired(true),
      'DECK_CODE' => (new StringField(
        'DECK_CODE',
        [
          'validation' => [__CLASS__, 'validateDeckCode']
        ]
      ))->configureTitle(Loc::getMessage('CABINS_ENTITY_DECK_CODE_FIELD'))
        ->configureRequired(true),
      'DECK_ELEMENT_ID' => (new IntegerField(
        'DECK_ELEMENT_ID',
        []
      ))->configureTitle(Loc::getMessage('CABINS_ENTITY_DECK_ELEMENT_ID_FIELD'))
        ->configureRequired(true),
    ];
  }

  /**
   * Returns validators for CABIN_CODE field.
   *
   * @return array
   */
  public static function validateCabinCode(): array
  {
    return [
      new LengthValidator(null, 50),
    ];
  }

  /**
   * Returns validators for DECK_CODE field.
   *
   * @return array
   */
  public static function validateDeckCode(): array
  {
    return [
      new LengthValidator(null, 10),
    ];
  }
}
