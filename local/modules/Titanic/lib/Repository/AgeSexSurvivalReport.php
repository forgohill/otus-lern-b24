<?php

declare(strict_types=1);

namespace Models\Titanic\Repository;

/**
 * Готовит отчет выживаемости по возрастным группам и полу.
 */
final class AgeSexSurvivalReport extends PassengersRepository
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
	 *   age_group: string,
	 *   sex: string,
	 *   total: int,
	 *   survived: int,
	 *   survival_rate: float
	 * }>
	 */
	public function getRows(array $filter = []): array
	{
		$groups = [];

		foreach ($this->getItems($this->getSourceFilter($filter)) as $item) {
			$ageGroup = $this->resolveAgeGroup($item['AGE'] ?? null);
			$sex = $this->resolveSex((string)($item['SEX'] ?? ''));
			$groupKey = $ageGroup . '|' . $sex;

			if (!isset($groups[$groupKey])) {
				$groups[$groupKey] = [
					'age_group' => $ageGroup,
					'sex' => $sex,
					'total' => 0,
					'survived' => 0,
				];
			}

			$groups[$groupKey]['total']++;
			$groups[$groupKey]['survived'] += (int)($item['SURVIVED'] ?? 0);
		}

		$rows = [];

		foreach ($groups as $group) {
			$total = (int)$group['total'];
			$survived = (int)$group['survived'];

			$rows[] = [
				'age_group' => (string)$group['age_group'],
				'sex' => (string)$group['sex'],
				'total' => $total,
				'survived' => $survived,
				'survival_rate' => $this->calculateSurvivalRate($survived, $total),
			];
		}

		usort($rows, [$this, 'compareRows']);

		return $rows;
	}

	/**
	 * Преобразует возраст в аналитическую группу.
	 *
	 * @param mixed $age
	 * @return string
	 */
	private function resolveAgeGroup(mixed $age): string
	{
		if ($age === null || $age === '') {
			return 'unknown';
		}

		if (!is_numeric($age)) {
			return 'unknown';
		}

		$age = (float)$age;

		if ($age <= 5) {
			return '0-5';
		}

		if ($age <= 12) {
			return '6-12';
		}

		if ($age <= 18) {
			return '13-18';
		}

		if ($age <= 30) {
			return '19-30';
		}

		if ($age <= 45) {
			return '31-45';
		}

		if ($age <= 60) {
			return '46-60';
		}

		return '60+';
	}

	/**
	 * Нормализует значение пола.
	 *
	 * @param string $sex
	 * @return string
	 */
	private function resolveSex(string $sex): string
	{
		$sex = trim($sex);

		if ($sex === '') {
			return 'unknown';
		}

		return $sex;
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

	/**
	 * @param array{age_group: string, sex: string} $left
	 * @param array{age_group: string, sex: string} $right
	 * @return int
	 */
	private function compareRows(array $left, array $right): int
	{
		$leftAgeOrder = $this->resolveAgeOrder($left['age_group']);
		$rightAgeOrder = $this->resolveAgeOrder($right['age_group']);

		if ($leftAgeOrder === $rightAgeOrder) {
			$leftSexOrder = $this->resolveSexOrder($left['sex']);
			$rightSexOrder = $this->resolveSexOrder($right['sex']);

			return $leftSexOrder <=> $rightSexOrder;
		}

		return $leftAgeOrder <=> $rightAgeOrder;
	}

	/**
	 * Возвращает порядок сортировки возрастной группы.
	 *
	 * @param string $ageGroup
	 * @return int
	 */
	private function resolveAgeOrder(string $ageGroup): int
	{
		return [
			'0-5' => 1,
			'6-12' => 2,
			'13-18' => 3,
			'19-30' => 4,
			'31-45' => 5,
			'46-60' => 6,
			'60+' => 7,
			'unknown' => 8,
		][$ageGroup] ?? 99;
	}

	/**
	 * Возвращает порядок сортировки пола.
	 *
	 * @param string $sex
	 * @return int
	 */
	private function resolveSexOrder(string $sex): int
	{
		return [
			'female' => 1,
			'male' => 2,
			'unknown' => 3,
		][$sex] ?? 99;
	}
}
