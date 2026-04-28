<?php

declare(strict_types=1);

namespace Models\Titanic\Repository;

/**
 * Готовит отчет выживаемости по порту посадки и классу пассажира.
 */
final class EmbarkedClassSurvivalReport extends PassengersRepository
{
	private const CLASS_CODE_TO_NUMBER = [
		'first' => 1,
		'second' => 2,
		'third' => 3,
	];

	private const PORT_SORT = [
		'C' => 1,
		'Q' => 2,
		'S' => 3,
	];

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

		foreach ($this->getItems($this->getSourceFilter($filter)) as $item) {
			$portCode = $this->normalizePortCode((string)($item['EMBARKED_CODE'] ?? ''));
			$pclassCode = (string)($item['PCLASS_CODE'] ?? '');
			$bucketKey = $portCode . '|' . $pclassCode;

			if (!isset($groups[$bucketKey])) {
				$groups[$bucketKey] = [
					'PORT' => $portCode,
					'PORT_NAME' => $item['EMBARKED_NAME'] ?? '',
					'PCLASS' => $this->resolveClassNumber($pclassCode),
					'PCLASS_CODE' => $pclassCode,
					'PCLASS_NAME' => $item['PCLASS_NAME'] ?? '',
					'TOTAL' => 0,
					'SURVIVED' => 0,
				];
			}

			$groups[$bucketKey]['TOTAL']++;
			$groups[$bucketKey]['SURVIVED'] += (int)($item['SURVIVED'] ?? 0);
		}

		$rows = [];

		foreach ($groups as $group) {
			$total = (int)$group['TOTAL'];
			$survived = (int)$group['SURVIVED'];

			$rows[] = [
				'PORT' => (string)$group['PORT'],
				'PORT_NAME' => (string)$group['PORT_NAME'],
				'PCLASS' => (int)$group['PCLASS'],
				'PCLASS_CODE' => (string)$group['PCLASS_CODE'],
				'PCLASS_NAME' => (string)$group['PCLASS_NAME'],
				'TOTAL' => $total,
				'SURVIVED' => $survived,
				'SURVIVAL_RATE' => $this->calculateSurvivalRate($survived, $total),
			];
		}

		usort($rows, [$this, 'compareRows']);

		return $rows;
	}

	private function normalizePortCode(string $portCode): string
	{
		$portCode = trim($portCode);

		return $portCode !== '' ? $portCode : 'unknown';
	}

	private function resolveClassNumber(string $classCode): int
	{
		return self::CLASS_CODE_TO_NUMBER[$classCode] ?? 0;
	}

	private function calculateSurvivalRate(int $survived, int $total): float
	{
		if ($total === 0) {
			return 0.0;
		}

		return round(($survived / $total) * 100, 1);
	}

	/**
	 * @param array{PORT: string, PCLASS: int} $left
	 * @param array{PORT: string, PCLASS: int} $right
	 */
	private function compareRows(array $left, array $right): int
	{
		$leftPortOrder = self::PORT_SORT[$left['PORT']] ?? 99;
		$rightPortOrder = self::PORT_SORT[$right['PORT']] ?? 99;

		return [$leftPortOrder, $left['PCLASS']] <=> [$rightPortOrder, $right['PCLASS']];
	}
}
