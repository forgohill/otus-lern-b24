<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

/** @var array $arCurrentValues */

use Bitrix\Currency\CurrencyTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('currency')) {
	return;
}

/** @var array<string, string> $currencyList Список валют в формате NUMCODE => CURRENCY. */
$currencyList = [];
$currencyResult = CurrencyTable::getList([
	'select' => [
		'CURRENCY',
		'NUMCODE',
	],
	'order' => [
		'SORT' => 'ASC',
		'CURRENCY' => 'ASC',
	],
]);

while ($currency = $currencyResult->fetch()) {
	$currencyList[$currency['NUMCODE']] = $currency['CURRENCY'];
}

$arComponentParameters = [
	'PARAMETERS' => [
		'CURRENCY' => [
			'PARENT' => 'BASE',
			'NAME' => Loc::getMessage('CURRENCY'),
			'TYPE' => 'LIST',
			'VALUES' => $currencyList,
			'DEFAULT' => 'RUB',
			'MULTIPLE' => 'N',
			'ADDITIONAL_VALUES' => 'N',
		],
	],
];
