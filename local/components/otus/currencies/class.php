<?php

use Bitrix\Currency\CurrencyTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

Loc::loadMessages(__FILE__);

/**
 * Компонент выводит текущий курс выбранной валюты.
 *
 * Параметр CURRENCY хранит числовой код валюты из поля NUMCODE.
 */
class CurrenciesComp extends CBitrixComponent
{
	/**
	 * Подготавливает параметры компонента перед выполнением.
	 *
	 * @param array $arParams Параметры, переданные в IncludeComponent.
	 *
	 * @return array
	 */
	public function onPrepareComponentParams($arParams)
	{
		$arParams['CURRENCY'] = (string)($arParams['CURRENCY'] ?? '643');

		return $arParams;
	}

	/**
	 * Загружает данные выбранной валюты и передает их в шаблон.
	 *
	 * @return void
	 */
	public function executeComponent()
	{
		if (!Loader::includeModule('currency')) {
			ShowError(Loc::getMessage('OTUS_CURRENCIES_MODULE_NOT_LOADED'));
			return;
		}

		if ($this->startResultCache()) {
			$currency = CurrencyTable::getList([
				'filter' => [
					'=NUMCODE' => $this->arParams['CURRENCY'],
				],
			])->fetch();

			if (!$currency) {
				$this->abortResultCache();
				ShowError(Loc::getMessage('OTUS_CURRENCIES_NOT_FOUND'));
				return;
			}

			$this->arResult['CURRENCY'] = [
				'CODE' => $currency['CURRENCY'],
				'RATE' => $currency['CURRENT_BASE_RATE'],
				'AMOUNT_CNT' => $currency['AMOUNT_CNT'],
			];

			$this->includeComponentTemplate();
		}
	}
}
