<?php

namespace Models\BookPublisher;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;

/**
 * Class PublisherTable
 * 
 * Fields:
 * <ul>
 * <li> id int mandatory
 * <li> book_id int optional
 * <li> publisher_id int optional
 * </ul>
 *
 * @package Bitrix\BookPublisher
 **/

class BookPublisherTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'book_publisher';
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
			))->configureTitle(Loc::getMessage('PUBLISHER_ENTITY_ID_FIELD'))
				->configurePrimary(true)
				->configureAutocomplete(true),
			'book_id' => (new IntegerField(
				'book_id',
				[]
			))->configureTitle(Loc::getMessage('PUBLISHER_ENTITY_BOOK_ID_FIELD')),
			'publisher_id' => (new IntegerField(
				'publisher_id',
				[]
			))->configureTitle(Loc::getMessage('PUBLISHER_ENTITY_PUBLISHER_ID_FIELD')),
		];
	}
}
