<?php

declare(strict_types=1);

namespace Models\Titanic\Repository;

use Bitrix\Main\ORM\Fields\ExpressionField;
use Models\Titanic\Orm\PassengersTable;

/**
 * Готовит агрегированный отчёт выживаемости по полу и классу пассажира.
 */
final class SexClassSurvivalReport
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
  * @return list<array{
  *   sex: string,
  *   pclass: int,
  *   pclass_code: string,
  *   total: int,
  *   survived: int,
  *   survival_rate: float
  * }>
  */
 public function getRows(): array
 {
  $result = PassengersTable::getList([
   'select' => [
    'SEX',
    'PCLASS_ELEMENT_ID',
    'PCLASS_CODE' => 'PCLASS_ELEMENT.CODE',
    'TOTAL',
    'SURVIVED_TOTAL',
   ],
   'runtime' => [
    new ExpressionField('TOTAL', 'COUNT(%s)', ['ID']),
    new ExpressionField('SURVIVED_TOTAL', 'SUM(%s)', ['SURVIVED']),
   ],
   'group' => [
    'SEX',
    'PCLASS_ELEMENT_ID',
    'PCLASS_ELEMENT.CODE',
   ],
   'order' => [
    'SEX' => 'ASC',
    'PCLASS_ELEMENT_ID' => 'ASC',
   ],
  ]);

  $rows = [];

  while ($row = $result->fetch()) {
   $total = (int)$row['TOTAL'];
   $survived = (int)$row['SURVIVED_TOTAL'];
   $pclassCode = (string)$row['PCLASS_CODE'];

   $rows[] = [
    'sex' => (string)$row['SEX'],
    'pclass' => $this->resolveClassNumber($pclassCode),
    'pclass_code' => $pclassCode,
    'total' => $total,
    'survived' => $survived,
    'survival_rate' => $this->calculateSurvivalRate($survived, $total),
   ];
  }

  usort($rows, [$this, 'compareRows']);

  return $rows;
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
  * @param array{sex: string, pclass: int} $left
  * @param array{sex: string, pclass: int} $right
  */
 private function compareRows(array $left, array $right): int
 {
  $leftSexOrder = self::SEX_SORT[$left['sex']] ?? 99;
  $rightSexOrder = self::SEX_SORT[$right['sex']] ?? 99;

  return [$leftSexOrder, $left['pclass']] <=> [$rightSexOrder, $right['pclass']];
 }
}
