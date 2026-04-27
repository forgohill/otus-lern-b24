<?php

namespace Models\Titanic\Orm;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Query\Join;

use Models\Titanic\Orm\TicketsTable as Ticket;
use Models\Titanic\Orm\CabinsTable as Cabins;

use Models\Titanic\Service\Iblock\TitanicClassesIblock as TitanicClasses;
use Models\Titanic\Service\Iblock\TitanicPortsIblock as TitanicPorts;
use Models\Titanic\Service\Iblock\TitanicCabinDecksIblock  as TitanicCabinDecks;

Loc::loadMessages(__FILE__);

/**
 * Class PassengersTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PASSENGER_EXTERNAL_ID int mandatory
 * <li> FULL_NAME string(255) mandatory
 * <li> SEX string(20) mandatory
 * <li> AGE double optional
 * <li> SIBSP int optional default 0
 * <li> PARCH int optional default 0
 * <li> FARE double optional default 0.00
 * <li> SURVIVED int optional default 0
 * <li> TICKET_ID int mandatory
 * <li> PCLASS_ELEMENT_ID int mandatory
 * <li> EMBARKED_ELEMENT_ID int optional
 * <li> CABIN_DECK_ELEMENT_ID int optional
 * <li> CABIN_RAW string(255) optional
 * </ul>
 *
 * @package Bitrix\Titanic
 **/

class PassengersTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'otus_titanic_passengers';
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
			))->configureTitle(Loc::getMessage('PASSENGERS_ENTITY_ID_FIELD'))
				->configurePrimary(true)
				->configureAutocomplete(true),
			'PASSENGER_EXTERNAL_ID' => (new IntegerField(
				'PASSENGER_EXTERNAL_ID',
				[]
			))->configureTitle(Loc::getMessage('PASSENGERS_ENTITY_PASSENGER_EXTERNAL_ID_FIELD'))
				->configureRequired(true),
			'FULL_NAME' => (new StringField(
				'FULL_NAME',
				[
					'validation' => [__CLASS__, 'validateFullName']
				]
			))->configureTitle(Loc::getMessage('PASSENGERS_ENTITY_FULL_NAME_FIELD'))
				->configureRequired(true),
			'SEX' => (new StringField(
				'SEX',
				[
					'validation' => [__CLASS__, 'validateSex']
				]
			))->configureTitle(Loc::getMessage('PASSENGERS_ENTITY_SEX_FIELD'))
				->configureRequired(true),
			'AGE' => (new FloatField(
				'AGE',
				[]
			))->configureTitle(Loc::getMessage('PASSENGERS_ENTITY_AGE_FIELD')),
			'SIBSP' => (new IntegerField(
				'SIBSP',
				[]
			))->configureTitle(Loc::getMessage('PASSENGERS_ENTITY_SIBSP_FIELD'))
				->configureDefaultValue(0),
			'PARCH' => (new IntegerField(
				'PARCH',
				[]
			))->configureTitle(Loc::getMessage('PASSENGERS_ENTITY_PARCH_FIELD'))
				->configureDefaultValue(0),
			'FARE' => (new FloatField(
				'FARE',
				[]
			))->configureTitle(Loc::getMessage('PASSENGERS_ENTITY_FARE_FIELD'))
				->configureDefaultValue(0.00),
			'SURVIVED' => (new IntegerField(
				'SURVIVED',
				[]
			))->configureTitle(Loc::getMessage('PASSENGERS_ENTITY_SURVIVED_FIELD'))
				->configureDefaultValue(0)
				->configureSize(1),
			'TICKET_ID' => (new IntegerField(
				'TICKET_ID',
				[]
			))->configureTitle(Loc::getMessage('PASSENGERS_ENTITY_TICKET_ID_FIELD'))
				->configureRequired(true),
			'PCLASS_ELEMENT_ID' => (new IntegerField(
				'PCLASS_ELEMENT_ID',
				[]
			))->configureTitle(Loc::getMessage('PASSENGERS_ENTITY_PCLASS_ELEMENT_ID_FIELD'))
				->configureRequired(true),
			'EMBARKED_ELEMENT_ID' => (new IntegerField(
				'EMBARKED_ELEMENT_ID',
				[]
			))->configureTitle(Loc::getMessage('PASSENGERS_ENTITY_EMBARKED_ELEMENT_ID_FIELD')),
			'CABIN_DECK_ELEMENT_ID' => (new IntegerField(
				'CABIN_DECK_ELEMENT_ID',
				[]
			))->configureTitle(Loc::getMessage('PASSENGERS_ENTITY_CABIN_DECK_ELEMENT_ID_FIELD')),
			'CABIN_RAW' => (new StringField(
				'CABIN_RAW',
				[
					'validation' => [__CLASS__, 'validateCabinRaw']
				]
			))->configureTitle(Loc::getMessage('PASSENGERS_ENTITY_CABIN_RAW_FIELD')),

			(new Reference('TICKET', Ticket::class, Join::on('this.TICKET_ID', 'ref.ID')))
				->configureJoinType('left'),

			(new Reference('PCLASS_ELEMENT', TitanicClasses::getEntityDataClass(), Join::on('this.PCLASS_ELEMENT_ID', 'ref.ID')))->configureJoinType('left'),

			(new Reference('EMBARKED_ELEMENT', TitanicPorts::getEntityDataClass(), Join::on('this.EMBARKED_ELEMENT_ID', 'ref.ID')))->configureJoinType('left'),

			(new Reference('CABIN_DECK_ELEMENT', TitanicCabinDecks::getEntityDataClass(), Join::on('this.CABIN_DECK_ELEMENT_ID', 'ref.ID')))->configureJoinType('left'),

			(new ManyToMany('CABINS', Cabins::class))
				->configureTableName('otus_titanic_passenger_cabin')
				->configureLocalPrimary('ID', 'PASSENGER_ID')
				->configureLocalReference('PASSENGER')
				->configureRemotePrimary('ID', 'CABIN_ID')
				->configureRemoteReference('CABIN'),
		];
	}

	/**
	 * Returns validators for FULL_NAME field.
	 *
	 * @return array
	 */
	public static function validateFullName(): array
	{
		return [
			new LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for SEX field.
	 *
	 * @return array
	 */
	public static function validateSex(): array
	{
		return [
			new LengthValidator(null, 20),
		];
	}

	/**
	 * Returns validators for CABIN_RAW field.
	 *
	 * @return array
	 */
	public static function validateCabinRaw(): array
	{
		return [
			new LengthValidator(null, 255),
		];
	}
}
