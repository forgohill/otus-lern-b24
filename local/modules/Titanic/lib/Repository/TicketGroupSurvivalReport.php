<?php

declare(strict_types=1);

namespace Models\Titanic\Repository;

/**
 * Готовит отчет выживаемости по размеру группы пассажиров с одинаковым билетом.
 */
final class TicketGroupSurvivalReport extends PassengersRepository
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
	 * @param array<string, mixed> $filter
	 * @return array<int, array<string, mixed>>
	 */
	public function getRows(array $filter = []): array
	{
		$groups = [];

		foreach ($this->getTicketGroups($filter) as $ticketGroup) {
			$ticketGroupSize = (int)$ticketGroup['TICKET_GROUP_SIZE'];

			if (!isset($groups[$ticketGroupSize])) {
				$groups[$ticketGroupSize] = [
					'TICKET_GROUP_SIZE' => $ticketGroupSize,
					'TOTAL' => 0,
					'SURVIVED' => 0,
				];
			}

			$groups[$ticketGroupSize]['TOTAL'] += (int)$ticketGroup['TOTAL'];
			$groups[$ticketGroupSize]['SURVIVED'] += (int)$ticketGroup['SURVIVED'];
		}

		ksort($groups);

		return array_map(
			fn(array $group): array => $this->formatSummaryRow($group),
			array_values($groups)
		);
	}

	/**
	 * Подборка "Пассажиры с одиночным билетом".
	 *
	 * @param array<string, mixed> $filter
	 * @return array<int, array<string, mixed>>
	 */
	public function getSingleTicketPassengers(array $filter = []): array
	{
		return $this->getPassengersByTicketGroupSize(1, $filter);
	}

	/**
	 * Подборка "Пассажиры с групповым билетом".
	 *
	 * @param array<string, mixed> $filter
	 * @return array<int, array<string, mixed>>
	 */
	public function getGroupTicketPassengers(array $filter = []): array
	{
		$passengers = [];

		foreach ($this->getTicketGroups($filter) as $ticketGroup) {
			if ((int)$ticketGroup['TICKET_GROUP_SIZE'] <= 1) {
				continue;
			}

			foreach ($ticketGroup['PASSENGERS'] as $passenger) {
				$passenger['TICKET_GROUP_SIZE'] = $ticketGroup['TICKET_GROUP_SIZE'];
				$passenger['TICKET_RAW'] = $ticketGroup['TICKET_RAW'];
				$passengers[] = $passenger;
			}
		}

		return $passengers;
	}

	/**
	 * Подборка "Самые большие группы по одному билету".
	 *
	 * @param array<string, mixed> $filter
	 * @return array<int, array<string, mixed>>
	 */
	public function getLargestTicketGroups(array $filter = [], int $limit = 10): array
	{
		$ticketGroups = $this->getTicketGroups($filter);

		usort(
			$ticketGroups,
			static fn(array $left, array $right): int => $right['TICKET_GROUP_SIZE'] <=> $left['TICKET_GROUP_SIZE']
				?: $right['TOTAL'] <=> $left['TOTAL']
		);

		return array_slice($ticketGroups, 0, $limit);
	}

	/**
	 * @param array<string, mixed> $filter
	 * @return array<int, array<string, mixed>>
	 */
	public function getTicketGroups(array $filter = []): array
	{
		$ticketGroups = [];
		$items = $this->getItems($this->getSourceFilter($filter));

		foreach ($items as $item) {
			$ticketRaw = trim((string)($item['TICKET_RAW'] ?? ''));
			$ticketKey = $ticketRaw !== '' ? $ticketRaw : 'unknown';

			if (!isset($ticketGroups[$ticketKey])) {
				$ticketGroups[$ticketKey] = [
					'TICKET_RAW' => $ticketKey,
					'TICKET_GROUP_SIZE' => 0,
					'TOTAL' => 0,
					'SURVIVED' => 0,
					'SURVIVAL_RATE' => 0.0,
					'PASSENGERS' => [],
				];
			}

			$ticketGroups[$ticketKey]['TICKET_GROUP_SIZE']++;
			$ticketGroups[$ticketKey]['TOTAL']++;
			$ticketGroups[$ticketKey]['SURVIVED'] += (int)($item['SURVIVED'] ?? 0);
			$ticketGroups[$ticketKey]['PASSENGERS'][] = [
				'PASSENGER_EXTERNAL_ID' => $item['PASSENGER_EXTERNAL_ID'] ?? null,
				'FULL_NAME' => $item['FULL_NAME'] ?? '',
				'SEX' => $item['SEX'] ?? '',
				'SURVIVED' => $item['SURVIVED'] ?? 0,
				'PCLASS_NAME' => $item['PCLASS_NAME'] ?? '',
				'PCLASS_CODE' => $item['PCLASS_CODE'] ?? '',
			];
		}

		foreach ($ticketGroups as &$ticketGroup) {
			$ticketGroup['SURVIVAL_RATE'] = $this->calculateSurvivalRate(
				(int)$ticketGroup['SURVIVED'],
				(int)$ticketGroup['TOTAL']
			);
		}
		unset($ticketGroup);

		return array_values($ticketGroups);
	}

	/**
	 * @param array<string, mixed> $filter
	 * @return array<int, array<string, mixed>>
	 */
	private function getPassengersByTicketGroupSize(int $ticketGroupSize, array $filter = []): array
	{
		$passengers = [];

		foreach ($this->getTicketGroups($filter) as $ticketGroup) {
			if ((int)$ticketGroup['TICKET_GROUP_SIZE'] !== $ticketGroupSize) {
				continue;
			}

			foreach ($ticketGroup['PASSENGERS'] as $passenger) {
				$passenger['TICKET_GROUP_SIZE'] = $ticketGroup['TICKET_GROUP_SIZE'];
				$passenger['TICKET_RAW'] = $ticketGroup['TICKET_RAW'];
				$passengers[] = $passenger;
			}
		}

		return $passengers;
	}

	/**
	 * @param array<string, int> $group
	 * @return array<string, int|float>
	 */
	private function formatSummaryRow(array $group): array
	{
		return [
			'TICKET_GROUP_SIZE' => $group['TICKET_GROUP_SIZE'],
			'TOTAL' => $group['TOTAL'],
			'SURVIVED' => $group['SURVIVED'],
			'SURVIVAL_RATE' => $this->calculateSurvivalRate($group['SURVIVED'], $group['TOTAL']),
		];
	}

	private function calculateSurvivalRate(int $survived, int $total): float
	{
		if ($total === 0) {
			return 0.0;
		}

		return round(($survived / $total) * 100, 1);
	}
}
