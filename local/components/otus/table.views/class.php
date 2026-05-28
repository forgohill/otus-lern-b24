<?php

use Bitrix\Main\SystemException;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\ORM\Fields\Relations\Relation;
use Models\Titanic\Orm\PassengersTable as Passengers;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

class TableViewsComponent extends \CBitrixComponent
{
	/**
	 * Подготавливает параметры компонента.
	 *
	 * @param array $arParams
	 * @return array
	 */
	public function onPrepareComponentParams($arParams)
	{
		$arParams['SHOW_CHECKBOXES'] = ($arParams['SHOW_CHECKBOXES'] ?? 'N') === 'Y' ? 'Y' : 'N';
		$arParams['NUM_PAGE'] = (int)($arParams['NUM_PAGE'] ?? 20);
		if ($arParams['NUM_PAGE'] <= 0) {
			$arParams['NUM_PAGE'] = 20;
		}

		return $arParams;
	}

	/**
	 * Возвращает колонки грида по полям PassengersTable.
	 *
	 * @return array
	 */
	private function getColumns(): array
	{
		$columns = [];

		foreach (Passengers::getMap() as $field) {
			if ($field instanceof Relation) {
				continue;
			}

			$columns[] = [
				'id' => $field->getName(),
				'name' => $field->getTitle() ?: $field->getName(),
				'default' => true,
			];
		}

		return $columns;
	}

	/**
	 * Возвращает список строк для текущей страницы.
	 *
	 * @param int $page
	 * @param int $limit
	 * @return array
	 */
	private function getList(int $page = 1, int $limit = 20): array
	{
		$offset = $limit * ($page - 1);
		$list = [];

		$data = Passengers::getList([
			'select' => [
				'ID',
				'PASSENGER_EXTERNAL_ID',
				'FULL_NAME',
				'SEX',
				'AGE',
				'SIBSP',
				'PARCH',
				'FARE',
				'SURVIVED',
				'TICKET_ID',
				'PCLASS_ELEMENT_ID',
				'EMBARKED_ELEMENT_ID',
				'CABIN_DECK_ELEMENT_ID',
				'CABIN_RAW',
			],
			'order' => ['ID' => 'ASC'],
			'limit' => $limit,
			'offset' => $offset,
		]);

		while ($item = $data->fetch()) {
			$list[] = [
				'data' => $item,
			];
		}

		return $list;
	}

	/**
	 * Точка входа в компонент.
	 */
	public function executeComponent()
	{
		try {
			$nav = new PageNavigation('report_list');
			$nav->allowAllRecords(false);
			$nav->setPageSize($this->arParams['NUM_PAGE']);
			$nav->initFromUri();

			$this->arResult['SHOW_ROW_CHECKBOXES'] = $this->arParams['SHOW_CHECKBOXES'] === 'Y';
			$this->arResult['COLUMNS'] = $this->getColumns();
			$this->arResult['NUM_PAGE'] = $this->arParams['NUM_PAGE'];
			$this->arResult['LISTS'] = $this->getList($nav->getCurrentPage(), $this->arResult['NUM_PAGE']);
			$this->arResult['COUNT'] = Passengers::getCount();

			$this->IncludeComponentTemplate();
		} catch (SystemException $e) {
			ShowError($e->getMessage());
		}
	}
}
