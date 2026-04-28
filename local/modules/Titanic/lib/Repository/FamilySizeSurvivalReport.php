<?php

declare(strict_types=1);

namespace Models\Titanic\Repository;

/**
 * Готовит отчет выживаемости по размеру семьи пассажира.
 */
final class FamilySizeSurvivalReport extends PassengersRepository
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
	 * Возвращает выживаемость по размеру семьи.
	 *
	 * @param array<string, mixed> $filter
	 * @return list<array{
	 *   family_size: int,
	 *   family_label: string,
	 *   total: int,
	 *   survived: int,
	 *   survival_rate: float
	 * }>
	 */
	public function getRows(array $filter = []): array
	{
		$groups = [
			1 => ['family_size' => 1, 'family_label' => '1 (одиночка)', 'total' => 0, 'survived' => 0],
			2 => ['family_size' => 2, 'family_label' => '2', 'total' => 0, 'survived' => 0],
			3 => ['family_size' => 3, 'family_label' => '3', 'total' => 0, 'survived' => 0],
			4 => ['family_size' => 4, 'family_label' => '4', 'total' => 0, 'survived' => 0],
			5 => ['family_size' => 5, 'family_label' => '5+', 'total' => 0, 'survived' => 0],
		];

		$items = $this->getItems($this->getSourceFilter($filter));

		foreach ($items as $item) {
			$familySize = (int)($item['SIBSP'] ?? 0) + (int)($item['PARCH'] ?? 0) + 1;
			$bucket = $this->resolveBucket($familySize);

			$groups[$bucket]['total']++;
			$groups[$bucket]['survived'] += (int)($item['SURVIVED'] ?? 0);
		}

		$rows = [];

		foreach ($groups as $group) {
			$rows[] = [
				'family_size' => $group['family_size'],
				'family_label' => $group['family_label'],
				'total' => $group['total'],
				'survived' => $group['survived'],
				'survival_rate' => $this->calculateSurvivalRate($group['survived'], $group['total']),
			];
		}

		return $rows;
	}

	/**
	 * Преобразует размер семьи в аналитическую группу.
	 *
	 * @param int $familySize
	 * @return int
	 */
	private function resolveBucket(int $familySize): int
	{
		if ($familySize <= 1) {
			return 1;
		}

		if ($familySize === 2) {
			return 2;
		}

		if ($familySize === 3) {
			return 3;
		}

		if ($familySize === 4) {
			return 4;
		}

		return 5;
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
