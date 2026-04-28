<?php

declare(strict_types=1);

namespace Models\Titanic\Repository;

use Bitrix\Main\ORM\Objectify\EntityObject;
use Models\Titanic\Orm\PassengersTable;

/**
 * Возвращает одного пассажира по внешнему идентификатору из CSV.
 *
 * Удобен как вспомогательный репозиторий для точечной выборки одного объекта.
 */
final class PassengerByExternalIdRepository
{
	/**
	 * Получает одного пассажира через fetchObject() по PASSENGER_EXTERNAL_ID.
	 *
	 * @return EntityObject|null
	 */
	public function getByExternalId(int $passengerExternalId): ?EntityObject
	{
		return PassengersTable::getList([
			'filter' => [
				'=PASSENGER_EXTERNAL_ID' => $passengerExternalId,
			],
			// 'limit' => 1,
			'select' => [
				'*',
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
				'TICKET',
				'PCLASS_ELEMENT_ID',
				'PCLASS_ELEMENT',
				'EMBARKED_ELEMENT_ID',
				'EMBARKED_ELEMENT',
				'CABIN_DECK_ELEMENT_ID',
				'CABIN_DECK_ELEMENT',
				'CABIN_RAW',
				'CABINS',
			],
		])->fetchObject();
	}

	/**
	 * Разбирает связанные каюты внутри объекта пассажира в обычный массив.
	 *
	 * @param EntityObject|null $passenger
	 * @return array<int, array<string, mixed>>
	 */
	public function getCabinsFromPassenger(?EntityObject $passenger): array
	{
		if ($passenger === null) {
			return [];
		}

		$cabins = [];

		foreach ($passenger->getCabins() as $cabin) {
			$cabins[] = [
				'ID' => $cabin->getId(),
				'CABIN_CODE' => $cabin->getCabinCode(),
				'DECK_CODE' => $cabin->getDeckCode(),
				'DECK_ELEMENT_ID' => $cabin->getDeckElementId(),
				'DECK_NAME' => $cabin->getDeckElement()?->getName(),
				'DECK_CODE_NAME' => $cabin->getDeckElement()?->getCode(),
			];
		}

		return $cabins;
	}
}
