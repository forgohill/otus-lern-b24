<?php

declare(strict_types=1);

namespace Models\Titanic\Repository;

/**
 * Готовит отчет выживаемости по титулу пассажира.
 */
final class TitleSurvivalReport extends PassengersRepository
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
	 *   title: string,
	 *   total: int,
	 *   survived: int,
	 *   survival_rate: float
	 * }>
	 */
	public function getRows(array $filter = []): array
	{
		$groups = [];

		foreach ($this->getItems($this->getSourceFilter($filter)) as $item) {
			$title = $this->extractTitle((string)($item['FULL_NAME'] ?? ''));

			if ($title === '') {
				continue;
			}

			if (!isset($groups[$title])) {
				$groups[$title] = [
					'title' => $title,
					'total' => 0,
					'survived' => 0,
				];
			}

			$groups[$title]['total']++;
			$groups[$title]['survived'] += (int)($item['SURVIVED'] ?? 0);
		}

		$rows = [];

		foreach ($groups as $group) {
			$total = (int)$group['total'];
			$survived = (int)$group['survived'];

			$rows[] = [
				'title' => (string)$group['title'],
				'total' => $total,
				'survived' => $survived,
				'survival_rate' => $this->calculateSurvivalRate($survived, $total),
			];
		}

		usort($rows, [$this, 'compareRows']);

		return $rows;
	}

	private function extractTitle(string $fullName): string
	{
		if (preg_match('/,\s*([A-Za-z]+)\./u', $fullName, $matches) !== 1) {
			return '';
		}

		return $matches[1];
	}

	private function calculateSurvivalRate(int $survived, int $total): float
	{
		if ($total === 0) {
			return 0.0;
		}

		return round(($survived / $total) * 100, 1);
	}

	/**
	 * @param array{title: string} $left
	 * @param array{title: string} $right
	 */
	private function compareRows(array $left, array $right): int
	{
		$leftOrder = $this->resolveOrder($left['title']);
		$rightOrder = $this->resolveOrder($right['title']);

		if ($leftOrder === $rightOrder) {
			return strcmp($left['title'], $right['title']);
		}

		return $leftOrder <=> $rightOrder;
	}

	private function resolveOrder(string $title): int
	{
		return [
			'Mr' => 1,
			'Mrs' => 2,
			'Miss' => 3,
			'Master' => 4,
			'Rev' => 5,
			'Dr' => 6,
		][$title] ?? 99;
	}
}
