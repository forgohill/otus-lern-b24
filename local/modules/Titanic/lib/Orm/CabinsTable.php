<?php

namespace Models\Titanic\Orm;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
// use Bitrix\Sign\Blank\Block\Reference; 

use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Query\Join;

use Models\Titanic\Orm\PassengersTable as Passengers;

use Models\Titanic\Service\Iblock\TitanicCabinDecksIblock  as TitanicCabinDecks;

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

      (new Reference('DECK_ELEMENT', TitanicCabinDecks::getEntityDataClass(), Join::on('this.DECK_ELEMENT_ID', 'ref.ID')))->configureJoinType('left'),

      (new ManyToMany('PASSENGERS', Passengers::class))
        ->configureTableName('otus_titanic_passenger_cabin')
        ->configureLocalPrimary('ID', 'CABIN_ID')
        ->configureLocalReference('CABIN')
        ->configureRemotePrimary('ID', 'PASSENGER_ID')
        ->configureRemoteReference('PASSENGER'),
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
