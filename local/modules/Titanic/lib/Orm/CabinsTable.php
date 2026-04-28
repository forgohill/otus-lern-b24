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
 * ORM-сущность для таблицы кают.
 *
 * Поля:
 * <ul>
 * <li> ID int, обязательное, первичный ключ
 * <li> CABIN_CODE string(50), обязательное
 * <li> DECK_CODE string(10), обязательное
 * <li> DECK_ELEMENT_ID int, обязательное
 * </ul>
 *
 * Связи:
 * <ul>
 * <li> `DECK_ELEMENT` - ссылка на элемент инфоблока палуб</li>
 * <li> `PASSENGERS` - связь многие-ко-многим с пассажирами</li>
 * </ul>
 *
 * @package Models\Titanic\Orm
 **/

class CabinsTable extends DataManager
{
  /**
   * Возвращает имя таблицы базы данных для сущности.
   *
   * @return string
   */
  public static function getTableName()
  {
    return 'otus_titanic_cabins';
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
   * Возвращает валидаторы для поля CABIN_CODE.
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
   * Возвращает валидаторы для поля DECK_CODE.
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
