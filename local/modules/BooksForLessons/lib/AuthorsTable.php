<?php

namespace Models\BooksForLessons;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class Table
 * 
 * Fields:
 * <ul>
 * <li> id int mandatory
 * <li> name string(50) optional
 * </ul>
 *
 * @package Bitrix\
 **/

class AuthorsTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'authors';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'id' => (new IntegerField(
				'id',
				[]
			))->configureTitle(Loc::getMessage('_ENTITY_ID_FIELD'))
				->configurePrimary(true)
				->configureAutocomplete(true),
			'name' => (new StringField(
				'name',
				[
					'validation' => [__CLASS__, 'validateName']
				]
			))->configureTitle(Loc::getMessage('_ENTITY_NAME_FIELD')),
		];
	}

	/**
	 * Returns validators for name field.
	 *
	 * @return array
	 */
	public static function validateName(): array
	{
		return [
			new LengthValidator(null, 50),
		];
	}
}
