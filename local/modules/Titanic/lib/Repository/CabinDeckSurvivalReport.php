<?php

declare(strict_types=1);

namespace Models\Titanic\Repository;

/**
 * Готовит отчет выживаемости по палубам и наличию каюты.
 */
final class CabinDeckSurvivalReport extends PassengersRepository
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
	 *   deck: string,
	 *   label: string,
	 *   total: int,
	 *   survived: int,
	 *   survival_rate: float
	 * }>
	 */
	public function getRows(array $filter = []): array
	{
		$groups = [
			'A' => ['deck' => 'A', 'label' => 'A', 'total' => 0, 'survived' => 0],
			'B' => ['deck' => 'B', 'label' => 'B', 'total' => 0, 'survived' => 0],
			'C' => ['deck' => 'C', 'label' => 'C', 'total' => 0, 'survived' => 0],
			'D' => ['deck' => 'D', 'label' => 'D', 'total' => 0, 'survived' => 0],
			'E' => ['deck' => 'E', 'label' => 'E', 'total' => 0, 'survived' => 0],
			'F' => ['deck' => 'F', 'label' => 'F', 'total' => 0, 'survived' => 0],
			'G' => ['deck' => 'G', 'label' => 'G', 'total' => 0, 'survived' => 0],
			'empty' => ['deck' => 'empty', 'label' => 'Cabin пустой', 'total' => 0, 'survived' => 0],
		];

		foreach ($this->getItems($this->getSourceFilter($filter)) as $item) {
			$deck = $this->resolveDeckCode($item);

			if (!isset($groups[$deck])) {
				$deck = 'empty';
			}

			$groups[$deck]['total']++;
			$groups[$deck]['survived'] += (int)($item['SURVIVED'] ?? 0);
		}

		$rows = [];

		foreach ($groups as $group) {
			$total = (int)$group['total'];
			$survived = (int)$group['survived'];

			$rows[] = [
				'deck' => (string)$group['deck'],
				'label' => (string)$group['label'],
				'total' => $total,
				'survived' => $survived,
				'survival_rate' => $this->calculateSurvivalRate($survived, $total),
			];
		}

		return $rows;
	}

	/**
	 * @return list<array{
	 *   bucket: string,
	 *   total: int,
	 *   survived: int,
	 *   survival_rate: float
	 * }>
	 */
	public function getCabinPresenceRows(array $filter = []): array
	{
		$groups = [
			'known' => ['bucket' => 'Пассажиры с известной каютой', 'total' => 0, 'survived' => 0],
			'empty' => ['bucket' => 'Пассажиры без указанной каюты', 'total' => 0, 'survived' => 0],
		];

		foreach ($this->getItems($this->getSourceFilter($filter)) as $item) {
			$bucket = $this->resolveCabinPresenceBucket($item);

			$groups[$bucket]['total']++;
			$groups[$bucket]['survived'] += (int)($item['SURVIVED'] ?? 0);
		}

		$rows = [];

		foreach ($groups as $group) {
			$total = (int)$group['total'];
			$survived = (int)$group['survived'];

			$rows[] = [
				'bucket' => (string)$group['bucket'],
				'total' => $total,
				'survived' => $survived,
				'survival_rate' => $this->calculateSurvivalRate($survived, $total),
			];
		}

		return $rows;
	}

	/**
	 * @param array<string, mixed> $item
	 */
	private function resolveDeckCode(array $item): string
	{
		$cabins = $item['CABINS'] ?? [];

		if (!is_array($cabins) || $cabins === []) {
			return 'empty';
		}

		foreach ($cabins as $cabin) {
			if (!is_array($cabin)) {
				continue;
			}

			$deckCode = trim((string)($cabin['DECK_CODE'] ?? ''));

			if ($deckCode !== '') {
				return strtoupper($deckCode);
			}
		}

		return 'empty';
	}

	/**
	 * @param array<string, mixed> $item
	 */
	private function resolveCabinPresenceBucket(array $item): string
	{
		$cabins = $item['CABINS'] ?? [];

		return is_array($cabins) && $cabins !== [] ? 'known' : 'empty';
	}

	private function calculateSurvivalRate(int $survived, int $total): float
	{
		if ($total === 0) {
			return 0.0;
		}

		return round(($survived / $total) * 100, 1);
	}
}
