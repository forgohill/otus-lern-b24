<?php

declare(strict_types=1);

namespace App\Clinic\Repository;

use App\Clinic\Config\ProcedureIblockFields;
use Bitrix\Iblock\Elements\ElementProceduresTable;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\SystemException;

/**
 * Репозиторий процедур.
 *
 * Чтение выполняется через D7 ORM-класс инфоблока procedures:
 * \Bitrix\Iblock\Elements\ElementProceduresTable
 *
 * Важно:
 * для работы этого класса у инфоблока procedures должен быть выставлен API_CODE = Procedures.
 */
class ProcedureRepository
{
 public function getAllForSelect(): array
 {
  $this->ensureIblockModuleLoaded();

  $collection = ElementProceduresTable::getList([
   'select' => ['ID', 'NAME'],
   'filter' => ['=ACTIVE' => 'Y'],
   'order' => ['NAME' => 'ASC'],
  ])->fetchCollection();

  if ($collection === null) {
   return [];
  }

  $result = [];

  foreach ($collection as $procedure) {
   $result[] = [
    'id' => (int)$procedure->get('ID'),
    'name' => (string)$procedure->get('NAME'),
   ];
  }

  return $result;
 }

 public function getByIds(array $ids): array
 {
  $ids = $this->normalizeIds($ids);

  if ($ids === []) {
   return [];
  }

  $this->ensureIblockModuleLoaded();

  $collection = ElementProceduresTable::getList([
   'select' => [
    'ID',
    'NAME',
    $this->buildPropertyValueSelect(ProcedureIblockFields::DESCRIPTION),
   ],
   'filter' => ['@ID' => $ids],
   'order' => ['NAME' => 'ASC'],
  ])->fetchCollection();

  if ($collection === null) {
   return [];
  }

  $rowsById = [];

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

 public function getAllNames(): array
 {
  $this->ensureIblockModuleLoaded();

  $collection = ElementProceduresTable::getList([
   'select' => ['ID', 'NAME'],
   'filter' => ['=ACTIVE' => 'Y'],
   'order' => ['NAME' => 'ASC'],
  ])->fetchCollection();

  if ($collection === null) {
   return [];
  }

  $result = [];

  foreach ($collection as $procedure) {
   $result[(int)$procedure->get('ID')] = (string)$procedure->get('NAME');
  }

  return $result;
 }

 private function ensureIblockModuleLoaded(): void
 {
  if (!Loader::includeModule('iblock')) {
   throw new SystemException('Модуль iblock не подключен.');
  }
 }

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

 private function buildPropertyValueSelect(string $propertyCode): string
 {
  return $propertyCode . '.VALUE';
 }

 private function extractPropertyValue(mixed $propertyValue): ?string
 {
  if ($propertyValue === null) {
   return null;
  }

  if (is_scalar($propertyValue)) {
   return $this->normalizeNullableString($propertyValue);
  }

  if (is_object($propertyValue)) {
   if (method_exists($propertyValue, 'getValue')) {
    return $this->normalizeNullableString($propertyValue->getValue());
   }

   if (method_exists($propertyValue, 'get')) {
    return $this->normalizeNullableString($propertyValue->get('VALUE'));
   }
  }

  return null;
 }

 private function normalizeNullableString(mixed $value): ?string
 {
  if ($value === null) {
   return null;
  }

  $value = trim((string)$value);

  return $value !== '' ? $value : null;
 }
}
