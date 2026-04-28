<?php

declare(strict_types=1);

namespace Models\Titanic\Repository;

/**
 * Готовит отчет выживаемости по признаку "один / не один".
 */
final class AloneSurvivalReport extends PassengersRepository
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
	 * @return list<array{
	 *   is_alone: int,
	 *   label: string,
	 *   total: int,
	 *   survived: int,
	 *   survival_rate: float
	 * }>
	 */
	public function getRows(array $filter = []): array
	{
		$groups = [
			0 => ['is_alone' => 0, 'label' => 'Не один', 'total' => 0, 'survived' => 0],
			1 => ['is_alone' => 1, 'label' => 'Один', 'total' => 0, 'survived' => 0],
		];

		foreach ($this->getItems($this->getSourceFilter($filter)) as $item) {
			$isAlone = $this->isAlone($item);

			$groups[$isAlone]['total']++;
			$groups[$isAlone]['survived'] += (int)($item['SURVIVED'] ?? 0);
		}

		$rows = [];

		foreach ($groups as $group) {
			$rows[] = [
				'is_alone' => $group['is_alone'],
				'label' => $group['label'],
				'total' => $group['total'],
				'survived' => $group['survived'],
				'survival_rate' => $this->calculateSurvivalRate($group['survived'], $group['total']),
			];
		}

		return $rows;
	}

	/**
	 * Определяет, был ли пассажир одиночкой.
	 *
	 * @param array<string, mixed> $item
	 * @return int
	 */
	private function isAlone(array $item): int
	{
		$sibsp = (int)($item['SIBSP'] ?? 0);
		$parch = (int)($item['PARCH'] ?? 0);

		return ($sibsp + $parch) === 0 ? 1 : 0;
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
