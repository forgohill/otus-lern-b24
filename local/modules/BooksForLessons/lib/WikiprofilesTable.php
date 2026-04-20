
<?php

namespace Bitrix\Wikiprofiles;

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
 * <li> wikiprofile_ru string(50) optional
 * <li> wikiprofile_en string(50) optional
 * <li> book_id int optional
 * </ul>
 *
 * @package Bitrix\
 **/

class WikiprofilesTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'wikiprofiles';
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
			'wikiprofile_ru' => (new StringField(
				'wikiprofile_ru',
				[
					'validation' => [__CLASS__, 'validateWikiprofileRu']
				]
			))->configureTitle(Loc::getMessage('_ENTITY_WIKIPROFILE_RU_FIELD')),
			'wikiprofile_en' => (new StringField(
				'wikiprofile_en',
				[
					'validation' => [__CLASS__, 'validateWikiprofileEn']
				]
			))->configureTitle(Loc::getMessage('_ENTITY_WIKIPROFILE_EN_FIELD')),
			'book_id' => (new IntegerField(
				'book_id',
				[]
			))->configureTitle(Loc::getMessage('_ENTITY_BOOK_ID_FIELD')),
		];
	}

	/**
	 * Returns validators for wikiprofile_ru field.
	 *
	 * @return array
	 */
	public static function validateWikiprofileRu(): array
	{
		return [
			new LengthValidator(null, 50),
		];
	}

	/**
	 * Returns validators for wikiprofile_en field.
	 *
	 * @return array
	 */
	public static function validateWikiprofileEn(): array
	{
		return [
			new LengthValidator(null, 50),
		];
	}
}
