<?php

namespace Models\BookAuthor;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;

/**
 * Class AuthorTable
 * 
 * Fields:
 * <ul>
 * <li> id int mandatory
 * <li> book_id int optional
 * <li> author_id int optional
 * </ul>
 *
 * @package Bitrix\Author
 **/

class BookAuthorTable extends DataManager
{
 /**
  * Returns DB table name for entity.
  *
  * @return string
  */
 public static function getTableName()
 {
  return 'book_author';
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
   ))->configureTitle(Loc::getMessage('AUTHOR_ENTITY_ID_FIELD'))
    ->configurePrimary(true)
    ->configureAutocomplete(true),
   'book_id' => (new IntegerField(
    'book_id',
    []
   ))->configureTitle(Loc::getMessage('AUTHOR_ENTITY_BOOK_ID_FIELD')),
   'author_id' => (new IntegerField(
    'author_id',
    []
   ))->configureTitle(Loc::getMessage('AUTHOR_ENTITY_AUTHOR_ID_FIELD')),
  ];
 }
}
