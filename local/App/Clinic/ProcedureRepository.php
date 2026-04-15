<?php

declare(strict_types=1);

namespace App\Clinic;

use Bitrix\Iblock\Elements\ElementProceduresTable;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

class ProcedureRepository
{
 public function getList(): array
 {
  $this->loadIblockModule();

  return ElementProceduresTable::getList([
   'select' => [
    'ID',
    'NAME',
    'CODE',
    'DESCRIPTION' => ClinicConfig::PROCEDURE_DESCRIPTION_VALUE,
   ],
   'filter' => [
    '=ACTIVE' => 'Y',
   ],
   'order' => [
    'ID' => 'ASC',
   ],
  ])->fetchAll();
 }

 public function getById(int $id): ?array
 {
  if ($id <= 0) {
   return null;
  }

  $this->loadIblockModule();

  $row = ElementProceduresTable::getByPrimary($id, [
   'select' => [
    'ID',
    'NAME',
    'CODE',
    'DESCRIPTION' => ClinicConfig::PROCEDURE_DESCRIPTION_VALUE,
   ],
  ])->fetch();

  return $row ?: null;
 }

 public function getByIds(array $ids): array
 {
  $ids = array_values(array_unique(array_filter(
   array_map('intval', $ids),
   static fn(int $id): bool => $id > 0
  )));

  if ($ids === []) {
   return [];
  }

  $this->loadIblockModule();

  return ElementProceduresTable::getList([
   'select' => [
    'ID',
    'NAME',
    'CODE',
    'DESCRIPTION' => ClinicConfig::PROCEDURE_DESCRIPTION_VALUE,
   ],
   'filter' => [
    '@ID' => $ids,
    '=ACTIVE' => 'Y',
   ],
   'order' => [
    'ID' => 'ASC',
   ],
  ])->fetchAll();
 }

 private function loadIblockModule(): void
 {
  if (!Loader::includeModule('iblock')) {
   throw new SystemException('Модуль iblock не подключен');
  }
 }
}
