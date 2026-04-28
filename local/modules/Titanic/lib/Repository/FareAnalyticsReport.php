<?php

declare(strict_types=1);

namespace Models\Titanic\Repository;

/**
 * Готовит подборки по полной цене билета и примерной цене на пассажира.
 */
final class FareAnalyticsReport extends PassengersRepository
{
	/**
	 * Дает дочернему классу точку для расширения фильтра источника.
	 *
	 * @param array<string, mixed> $filter
	 * @return array<string, mixed>
	 */
	protected function getSourceFilter(array $filter = []): array
	{
		return $filter;
	}

	/**
	 * Подборка "Самые дорогие билеты".
	 *
	 * @param array<string, mixed> $filter
	 * @return array<int, array<string, mixed>>
	 */
	public function getMostExpensiveTickets(array $filter = [], int $limit = 10): array
	{
		$rows = $this->getRows($filter);

		usort(
			$rows,
			static fn(array $left, array $right): int => $right['FARE'] <=> $left['FARE']
		);

		return array_slice($rows, 0, $limit);
	}

	/**
	 * Подборка "Самые дорогие билеты на человека".
	 *
	 * @param array<string, mixed> $filter
	 * @return array<int, array<string, mixed>>
	 */
	public function getMostExpensivePerPassenger(array $filter = [], int $limit = 10): array
	{
		$rows = $this->getRows($filter);

		usort(
			$rows,
			static fn(array $left, array $right): int => $right['FARE_PER_PASSENGER'] <=> $left['FARE_PER_PASSENGER']
		);

		return array_slice($rows, 0, $limit);
	}

	/**
	 * Подборка "Пассажиры с Fare = 0".
	 *
	 * @param array<string, mixed> $filter
	 * @return array<int, array<string, mixed>>
	 */
	public function getZeroFarePassengers(array $filter = []): array
	{
		return array_values(array_filter(
			$this->getRows($filter),
			static fn(array $row): bool => (float)$row['FARE'] === 0.0
		));
	}

	/**
	 * Возвращает строки с аналитикой по стоимости билетов.
	 *
	 * @param array<string, mixed> $filter
	 * @return array<int, array<string, mixed>>
	 */
	public function getRows(array $filter = []): array
	{
		$items = $this->getItems($this->getSourceFilter($filter));
		$ticketGroupSizes = $this->buildTicketGroupSizes($items);
		$rows = [];

		foreach ($items as $item) {
			$ticketRaw = trim((string)($item['TICKET_RAW'] ?? ''));
			$ticketKey = $ticketRaw !== '' ? $ticketRaw : 'unknown';
			$ticketGroupSize = $ticketGroupSizes[$ticketKey] ?? 1;
			$fare = (float)($item['FARE'] ?? 0.0);

			$rows[] = [
				'PASSENGER_EXTERNAL_ID' => $item['PASSENGER_EXTERNAL_ID'] ?? null,
				'FULL_NAME' => $item['FULL_NAME'] ?? '',
				'SURVIVED' => $item['SURVIVED'] ?? 0,
				'PCLASS_NAME' => $item['PCLASS_NAME'] ?? '',
				'PCLASS_CODE' => $item['PCLASS_CODE'] ?? '',
				'TICKET_RAW' => $ticketKey,
				'TICKET_GROUP_SIZE' => $ticketGroupSize,
				'FARE' => $fare,
				'FARE_PER_PASSENGER' => $this->calculateFarePerPassenger($fare, $ticketGroupSize),
			];
		}

		return $rows;
	}

	/**
	 * Подсчитывает размер каждой группы по Ticket.
	 *
	 * @param array<int, array<string, mixed>> $items
	 * @return array<string, int>
	 */
	private function buildTicketGroupSizes(array $items): array
	{
		$ticketGroupSizes = [];

		foreach ($items as $item) {
			$ticketRaw = trim((string)($item['TICKET_RAW'] ?? ''));
			$ticketKey = $ticketRaw !== '' ? $ticketRaw : 'unknown';

			if (!isset($ticketGroupSizes[$ticketKey])) {
				$ticketGroupSizes[$ticketKey] = 0;
			}

			$ticketGroupSizes[$ticketKey]++;
		}

		return $ticketGroupSizes;
	}

	/**
	 * Считает стоимость билета на одного пассажира.
	 *
	 * @param float $fare
	 * @param int $ticketGroupSize
	 * @return float
	 */
	private function calculateFarePerPassenger(float $fare, int $ticketGroupSize): float
	{
		if ($ticketGroupSize <= 0) {
			return 0.0;
		}

		return round($fare / $ticketGroupSize, 4);
	}
}
