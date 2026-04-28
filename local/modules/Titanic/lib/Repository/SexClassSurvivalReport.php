<?php

declare(strict_types=1);

namespace Models\Titanic\Repository;

/**
 * Готовит агрегированный отчёт выживаемости по полу и классу пассажира.
 */
final class SexClassSurvivalReport extends PassengersRepository
{
 private const CLASS_CODE_TO_NUMBER = [
  'first' => 1,
  'second' => 2,
  'third' => 3,
 ];

 private const SEX_SORT = [
  'female' => 1,
  'male' => 2,
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
  * Возвращает выживаемость по полу и классу пассажира.
  *
  * @param array<string, mixed> $filter
  * @return list<array{
  *   sex: string,
  *   pclass: int,
  *   pclass_code: string,
  *   total: int,
  *   survived: int,
  *   survival_rate: float
  * }>
  */
 public function getRows(array $filter = []): array
 {
  $groups = [];

  foreach ($this->getItems($this->getSourceFilter($filter)) as $item) {
   $sex = (string)($item['SEX'] ?? '');
   $pclassCode = (string)($item['PCLASS_CODE'] ?? '');
   $bucketKey = $sex . '|' . $pclassCode;

   if (!isset($groups[$bucketKey])) {
    $groups[$bucketKey] = [
     'sex' => $sex,
     'pclass' => $this->resolveClassNumber($pclassCode),
     'pclass_code' => $pclassCode,
     'total' => 0,
     'survived' => 0,
    ];
   }

   $groups[$bucketKey]['total']++;
   $groups[$bucketKey]['survived'] += (int)($item['SURVIVED'] ?? 0);
  }

  $rows = [];

  foreach ($groups as $group) {
   $total = (int)$group['total'];
   $survived = (int)$group['survived'];

   $rows[] = [
    'sex' => (string)$group['sex'],
    'pclass' => (int)$group['pclass'],
    'pclass_code' => (string)$group['pclass_code'],
    'total' => $total,
    'survived' => $survived,
    'survival_rate' => $this->calculateSurvivalRate($survived, $total),
   ];
  }

  usort($rows, [$this, 'compareRows']);

  return $rows;
 }

 /**
  * Преобразует код класса в числовой порядок.
  *
  * @param string $classCode
  * @return int
  */
 private function resolveClassNumber(string $classCode): int
 {
  return self::CLASS_CODE_TO_NUMBER[$classCode] ?? 0;
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
  * @param array{sex: string, pclass: int} $left
  * @param array{sex: string, pclass: int} $right
  * @return int
  */
 private function compareRows(array $left, array $right): int
 {
  $leftSexOrder = self::SEX_SORT[$left['sex']] ?? 99;
  $rightSexOrder = self::SEX_SORT[$right['sex']] ?? 99;

  return [$leftSexOrder, $left['pclass']] <=> [$rightSexOrder, $right['pclass']];
 }
}
