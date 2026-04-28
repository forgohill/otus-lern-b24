<?php

declare(strict_types=1);

namespace Models\Titanic\Repository;

/**
 * Находит пассажиров, у которых через ORM-связь CABINS найдено больше одной каюты.
 */
final class MultiCabinPassengersReport extends PassengersRepository
{
	/**
	 * @param array<string, mixed> $filter
	 * @return array<int, array<string, mixed>>
	 */
	public function getRows(array $filter = []): array
	{
		$rows = [];

		foreach ($this->getFilteredCollection($filter) as $passenger) {
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

			$cabinCount = count($cabins);

			if ($cabinCount <= 1) {
				continue;
			}

			$rows[] = [
				'PASSENGER_EXTERNAL_ID' => $passenger->getPassengerExternalId(),
				'FULL_NAME' => $passenger->getFullName(),
				'PCLASS_NAME' => $passenger->getPclassElement()?->getName(),
				'PCLASS_CODE' => $passenger->getPclassElement()?->getCode(),
				'CABIN_RAW' => $passenger->getCabinRaw(),
				'CABIN_COUNT' => $cabinCount,
				'CABINS' => $cabins,
			];
		}

		return $rows;
	}

	/**
	 * Группирует пассажиров с несколькими каютами по одинаковому набору кают.
	 *
	 * @param array<string, mixed> $filter
	 * @return array<int, array<string, mixed>>
	 */
	public function getFamilyCabinGroups(array $filter = []): array
	{
		$groups = [];

		foreach ($this->getRows($filter) as $row) {
			$cabinCodes = array_map(
				static fn(array $cabin): string => (string)($cabin['CABIN_CODE'] ?? ''),
				is_array($row['CABINS']) ? $row['CABINS'] : []
			);
			$cabinCodes = array_values(array_filter($cabinCodes, static fn(string $code): bool => $code !== ''));
			sort($cabinCodes);

			$groupKey = implode('|', $cabinCodes);

			if ($groupKey === '') {
				continue;
			}

			if (!isset($groups[$groupKey])) {
				$groups[$groupKey] = [
					'CABIN_RAW' => $row['CABIN_RAW'],
					'CABIN_COUNT' => count($cabinCodes),
					'CABIN_CODES' => $cabinCodes,
					'PASSENGER_COUNT' => 0,
					'PASSENGERS' => [],
				];
			}

			$groups[$groupKey]['PASSENGER_COUNT']++;
			$groups[$groupKey]['PASSENGERS'][] = [
				'PASSENGER_EXTERNAL_ID' => $row['PASSENGER_EXTERNAL_ID'],
				'FULL_NAME' => $row['FULL_NAME'],
				'PCLASS_NAME' => $row['PCLASS_NAME'],
				'PCLASS_CODE' => $row['PCLASS_CODE'],
			];
		}

		usort(
			$groups,
			static fn(array $left, array $right): int => $right['PASSENGER_COUNT'] <=> $left['PASSENGER_COUNT']
				?: $right['CABIN_COUNT'] <=> $left['CABIN_COUNT']
		);

		return array_values($groups);
	}

	/**
	 * Подборка "Пассажиры с несколькими каютами".
	 *
	 * @param array<string, mixed> $filter
	 * @return array<int, array<string, mixed>>
	 */
	public function getPassengerList(array $filter = []): array
	{
		return $this->getRows($filter);
	}
}
