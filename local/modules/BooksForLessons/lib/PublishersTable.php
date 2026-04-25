<?php

namespace Models\BooksForLessons;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

use Bitrix\Main\ORM\Fields\Relations\OneToMany;

use Models\BooksForLessons\BooksTable as Books;


/**
 * ORM-модель таблицы `publishers`.
 *
 * Fields:
 * <ul>
 * <li> id int mandatory
 * <li> name string(50) optional
 * </ul>
 *
 * @package Models\BooksForLessons
 **/

class PublishersTable extends DataManager
{
	/**
	 * Возвращает имя таблицы БД для сущности.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'publishers';
	}

	/**
	 * Возвращает описание полей и связей сущности.
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
			(new OneToMany('BOOKS', Books::class, 'PUBLISHER'))
				->configureJoinType('inner')
		];
	}

	/**
	 * Возвращает валидаторы для поля `name`.
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
