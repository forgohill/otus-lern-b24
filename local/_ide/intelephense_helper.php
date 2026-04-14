<?php

namespace Bitrix\Iblock\Elements;

use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\DeleteResult;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\ORM\Query\Result;

/**
 * IDE helper only.
 * Не подключать через require/include.
 */
abstract class ElementCarTable
{
 abstract public static function getList(array $parameters = []): Result;

 abstract public static function query(): Query;

 abstract public static function add(array $data): AddResult;

 abstract public static function update($primary, array $data): UpdateResult;

 abstract public static function delete($primary): DeleteResult;

 abstract public static function getByPrimary($primary, array $parameters = []): Result;

 abstract public static function createObject($setDefaultValues = true): EntityObject;
}

/**
 * IDE helper only.
 * Не подключать через require/include.
 */
abstract class ElementCountryTable
{
 abstract public static function getList(array $parameters = []): Result;

 abstract public static function query(): Query;

 abstract public static function add(array $data): AddResult;

 abstract public static function update($primary, array $data): UpdateResult;

 abstract public static function delete($primary): DeleteResult;

 abstract public static function getByPrimary($primary, array $parameters = []): Result;

 abstract public static function createObject($setDefaultValues = true): EntityObject;
}

/**
 * IDE helper only.
 * Не подключать через require/include.
 */
abstract class ElementDoctorsTable
{
 abstract public static function getList(array $parameters = []): Result;

 abstract public static function query(): Query;

 abstract public static function add(array $data): AddResult;

 abstract public static function update($primary, array $data): UpdateResult;

 abstract public static function delete($primary): DeleteResult;

 abstract public static function getByPrimary($primary, array $parameters = []): Result;

 abstract public static function createObject($setDefaultValues = true): EntityObject;
}

/**
 * IDE helper only.
 * Не подключать через require/include.
 */
abstract class ElementProceduresTable
{
 abstract public static function getList(array $parameters = []): Result;

 abstract public static function query(): Query;

 abstract public static function add(array $data): AddResult;

 abstract public static function update($primary, array $data): UpdateResult;

 abstract public static function delete($primary): DeleteResult;

 abstract public static function getByPrimary($primary, array $parameters = []): Result;

 abstract public static function createObject($setDefaultValues = true): EntityObject;
}
