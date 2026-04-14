<?php

declare(strict_types=1);

namespace App\Clinic\Repository;

use App\Clinic\Config\ClinicIblockCodes;
use App\Clinic\Config\ProcedureIblockFields;
use App\Iblock\Repository\AbstractIblockRepository;
use Bitrix\Main\ORM\Objectify\EntityObject;

/**
 * Репозиторий процедур.
 *
 * Нужен для:
 * - списка процедур в select формы врача;
 * - получения процедур по массиву ID;
 * - получения списка названий процедур.
 */
class ProcedureRepository extends AbstractIblockRepository
{
 /**
  * Возвращает символьный код инфоблока процедур.
  */
 public function getIblockCode(): string
 {
  return ClinicIblockCodes::PROCEDURES;
 }

 /**
  * Возвращает все активные процедуры для select.
  *
  * Формат:
  * [
  *   ['id' => 12, 'name' => 'Первичный осмотр'],
  *   ['id' => 15, 'name' => 'ЭКГ'],
  * ]
  *
  * @return array<int, array{id:int,name:string}>
  */
 public function getAllForSelect(): array
 {
  $collection = $this->getList([
   'select' => ['ID', 'NAME'],
   'filter' => ['=ACTIVE' => 'Y'],
   'order'  => ['NAME' => 'ASC'],
  ])->fetchCollection();

  $result = [];

  if ($collection === null) {
   return [];
  }

  foreach ($collection as $procedure) {
   $result[] = [
    'id'   => (int)$procedure->get('ID'),
    'name' => (string)$procedure->get('NAME'),
   ];
  }

  return $result;
 }

 /**
  * Возвращает процедуры по массиву ID.
  *
  * Формат:
  * [
  *   [
  *     'id' => 5,
  *     'name' => 'Снятие ЭКГ',
  *     'description' => 'Описание процедуры',
  *   ],
  * ]
  *
  * Важно:
  * - сохраняем порядок входных ID;
  * - не падаем на пустом массиве;
  * - отсекаем мусорные значения.
  *
  * @param array<int, mixed> $ids
  * @return array<int, array{id:int,name:string,description:?string}>
  */
 public function getByIds(array $ids): array
 {
  $ids = $this->normalizeIds($ids);

  if ($ids === []) {
   return [];
  }

  $collection = $this->getList([
   'select' => [
    'ID',
    'NAME',
    ProcedureIblockFields::DESCRIPTION,
   ],
   'filter' => ['@ID' => $ids],
   'order'  => ['NAME' => 'ASC'],
  ])->fetchCollection();

  $rowsById = [];

  if ($collection === null) {
   return [];
  }

  foreach ($collection as $procedure) {
   $row = $this->mapProcedure($procedure);
   $rowsById[$row['id']] = $row;
  }

  $result = [];

  foreach ($ids as $id) {
   if (isset($rowsById[$id])) {
    $result[] = $rowsById[$id];
   }
  }

  return $result;
 }

 /**
  * Возвращает массив названий процедур в формате:
  * [
  *   5 => 'Снятие ЭКГ',
  *   7 => 'Повторный приём',
  * ]
  *
  * @return array<int, string>
  */
 public function getAllNames(): array
 {
  $collection = $this->getList([
   'select' => ['ID', 'NAME'],
   'filter' => ['=ACTIVE' => 'Y'],
   'order'  => ['NAME' => 'ASC'],
  ])->fetchCollection();

  $result = [];

  if ($collection === null) {
   return [];
  }

  foreach ($collection as $procedure) {
   $result[(int)$procedure->get('ID')] = (string)$procedure->get('NAME');
  }

  return $result;
 }

 /**
  * Приводит массив ID к чистому виду:
  * - int
  * - > 0
  * - без дублей
  *
  * @param array<int, mixed> $ids
  * @return int[]
  */
 private function normalizeIds(array $ids): array
 {
  $result = [];

  foreach ($ids as $id) {
   $id = (int)$id;

   if ($id > 0) {
    $result[] = $id;
   }
  }

  return array_values(array_unique($result));
 }

 /**
  * Собирает одну процедуру в нормальный массив.
  *
  * @return array{id:int,name:string,description:?string}
  */
 private function mapProcedure(EntityObject $procedure): array
 {
  return [
   'id' => (int)$procedure->get('ID'),
   'name' => (string)$procedure->get('NAME'),
   'description' => $this->extractPropertyValue(
    $procedure->get(ProcedureIblockFields::DESCRIPTION)
   ),
  ];
 }

 /**
  * Достаёт value у свойства инфоблока.
  *
  * На случай, если Битрикс вернёт:
  * - объект свойства;
  * - скаляр;
  * - null.
  */
 private function extractPropertyValue(mixed $propertyValue): ?string
 {
  if ($propertyValue === null) {
   return null;
  }

  if (is_scalar($propertyValue)) {
   $value = trim((string)$propertyValue);

   return $value !== '' ? $value : null;
  }

  if (is_object($propertyValue) && method_exists($propertyValue, 'getValue')) {
   $value = $propertyValue->getValue();

   if ($value === null) {
    return null;
   }

   $value = trim((string)$value);

   return $value !== '' ? $value : null;
  }

  return null;
 }
}
