<?php

namespace Bitrix\BookStore;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;

/**
 * Class BookStoreTable
 * 
 * Fields:
 * <ul>
 * <li> id int mandatory
 * <li> book_id int optional
 * <li> store_id int optional
 * </ul>
 *
 * @package Bitrix\BookStore
 **/

class BookStoreTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'book_store';
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
			))->configureTitle(Loc::getMessage('STORE_ENTITY_ID_FIELD'))
				->configurePrimary(true)
				->configureAutocomplete(true),
			'book_id' => (new IntegerField(
				'book_id',
				[]
			))->configureTitle(Loc::getMessage('STORE_ENTITY_BOOK_ID_FIELD')),
			'store_id' => (new IntegerField(
				'store_id',
				[]
			))->configureTitle(Loc::getMessage('STORE_ENTITY_STORE_ID_FIELD')),
		];
	}
}
