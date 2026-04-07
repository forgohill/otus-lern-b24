<?php

namespace Bitrix\Iblock\Elements;

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
}
