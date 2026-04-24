<?php

namespace Models\BooksForLessons;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Query\Join;

use Models\BooksForLessons\BooksTable as Books;


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

class PublishersTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'publishers';
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
			(new ManyToMany('BOOKS', Books::class))
				->configureTableName('book_publisher')
				->configureLocalPrimary('id', 'publisher_id')
				->configureLocalReference('PUBLISHERS')
				->configureRemotePrimary('id', 'book_id')
				->configureRemoteReference('BOOKS'),
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
