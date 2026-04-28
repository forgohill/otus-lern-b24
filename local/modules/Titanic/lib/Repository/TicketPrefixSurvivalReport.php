<?php

declare(strict_types=1);

namespace Models\Titanic\Repository;

/**
 * Готовит отчет выживаемости по префиксу билета.
 */
final class TicketPrefixSurvivalReport extends PassengersRepository
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
	 * Возвращает выживаемость по префиксу билета.
	 *
	 * @param array<string, mixed> $filter
	 * @return array<int, array<string, mixed>>
	 */
	public function getRows(array $filter = []): array
	{
		$groups = [];

		foreach ($this->getItems($this->getSourceFilter($filter)) as $item) {
			$prefix = $this->normalizePrefix((string)($item['TICKET_PREFIX'] ?? ''));

			if (!isset($groups[$prefix])) {
				$groups[$prefix] = [
					'PREFIX' => $prefix,
					'TOTAL' => 0,
					'SURVIVED' => 0,
				];
			}

			$groups[$prefix]['TOTAL']++;
			$groups[$prefix]['SURVIVED'] += (int)($item['SURVIVED'] ?? 0);
		}

		$rows = [];

		foreach ($groups as $group) {
			$total = (int)$group['TOTAL'];
			$survived = (int)$group['SURVIVED'];

			$rows[] = [
				'PREFIX' => (string)$group['PREFIX'],
				'TOTAL' => $total,
				'SURVIVED' => $survived,
				'SURVIVAL_RATE' => $this->calculateSurvivalRate($survived, $total),
			];
		}

		usort(
			$rows,
			static fn(array $left, array $right): int => $right['TOTAL'] <=> $left['TOTAL']
				?: strcmp((string)$left['PREFIX'], (string)$right['PREFIX'])
		);

		return $rows;
	}

	/**
	 * Нормализует префикс билета.
	 *
	 * @param string $prefix
	 * @return string
	 */
	private function normalizePrefix(string $prefix): string
	{
		$prefix = trim($prefix);

		return $prefix !== '' ? $prefix : 'без префикса';
	}

	/**
	 * Считает процент выживаемости.
	 *
	 * @param int $survived
	 * @param int $total
	 * @return float
	 */
	private function calculateSurvivalRate(int $survived, int $total): float
	{
		if ($total === 0) {
			return 0.0;
		}

		return round(($survived / $total) * 100, 1);
	}
}
