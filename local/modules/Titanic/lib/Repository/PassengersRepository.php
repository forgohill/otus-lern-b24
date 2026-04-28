<?php

declare(strict_types=1);

namespace Models\Titanic\Repository;

use Bitrix\Main\ORM\Objectify\Collection;
use Models\Titanic\Orm\PassengersTable;

/**
 * Возвращает полную коллекцию пассажиров через Bitrix ORM.
 */
final class PassengersRepository
{
	/**
	 * @param array<string, mixed> $filter
	 * @return Collection
	 */
	public function getCollection(array $filter = []): Collection
	{
		$collection = PassengersTable::getList([
			'filter' => $filter,
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
		])->fetchCollection();

		if ($collection === null) {
			throw new \RuntimeException('Не удалось получить коллекцию пассажиров.');
		}

		return $collection;
	}

	/**
	 * Возвращает массив пассажиров из коллекции с вложенными связанными объектами.
	 *
	 * @param array<string, mixed> $filter
	 * @return array<int, array<string, mixed>>
	 */
	public function getItems(array $filter = []): array
	{
		$collectionItems = [];

		foreach ($this->getCollection($filter) as $passengerItem) {
			$cabins = [];

			foreach ($passengerItem->getCabins() as $cabin) {
				$cabins[] = [
					'ID' => $cabin->getId(),
					'CABIN_CODE' => $cabin->getCabinCode(),
					'DECK_CODE' => $cabin->getDeckCode(),
					'DECK_ELEMENT_ID' => $cabin->getDeckElementId(),
					'DECK_NAME' => $cabin->getDeckElement()?->getName(),
					'DECK_CODE_NAME' => $cabin->getDeckElement()?->getCode(),
				];
			}

			$collectionItems[] = [
				'ID' => $passengerItem->getId(),
				'PASSENGER_EXTERNAL_ID' => $passengerItem->getPassengerExternalId(),
				'FULL_NAME' => $passengerItem->getFullName(),
				'SEX' => $passengerItem->getSex(),
				'AGE' => $passengerItem->getAge(),
				'SIBSP' => $passengerItem->getSibsp(),
				'PARCH' => $passengerItem->getParch(),
				'FARE' => $passengerItem->getFare(),
				'SURVIVED' => $passengerItem->getSurvived(),
				'TICKET_ID' => $passengerItem->getTicketId(),
				'TICKET_RAW' => $passengerItem->getTicket()?->getTicketRaw(),
				'TICKET_PREFIX' => $passengerItem->getTicket()?->getTicketPrefix(),
				'TICKET_NUMBER' => $passengerItem->getTicket()?->getTicketNumber(),
				'TICKET_PASSENGER_COUNT' => $passengerItem->getTicket()?->getPassengerCount(),
				'TICKET_FARE_TOTAL' => $passengerItem->getTicket()?->getFareTotal(),
				'PCLASS_ELEMENT_ID' => $passengerItem->getPclassElementId(),
				'PCLASS_NAME' => $passengerItem->getPclassElement()?->getName(),
				'PCLASS_CODE' => $passengerItem->getPclassElement()?->getCode(),
				'EMBARKED_ELEMENT_ID' => $passengerItem->getEmbarkedElementId(),
				'EMBARKED_NAME' => $passengerItem->getEmbarkedElement()?->getName(),
				'EMBARKED_CODE' => $passengerItem->getEmbarkedElement()?->getCode(),
				'CABIN_DECK_ELEMENT_ID' => $passengerItem->getCabinDeckElementId(),
				'CABIN_DECK_NAME' => $passengerItem->getCabinDeckElement()?->getName(),
				'CABIN_DECK_CODE' => $passengerItem->getCabinDeckElement()?->getCode(),
				'CABINS' => $cabins,
			];
		}

		return $collectionItems;
	}
}
