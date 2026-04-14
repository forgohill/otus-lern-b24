<?php

declare(strict_types=1);

namespace App\Clinic;

use Bitrix\Iblock\Elements\ElementProceduresTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

class ProcedureService
{
 public function create(array $data): array
 {
  $this->loadIblockModule();

  $name = trim((string)($data['name'] ?? ''));
  $description = trim((string)($data['description'] ?? ''));

  if ($name === '') {
   return [
    'success' => false,
    'id' => null,
    'errors' => ['Не заполнено название процедуры'],
   ];
  }

  $procedure = ElementProceduresTable::createObject();

  $procedure->set('IBLOCK_ID', $this->getProcedureIblockId());
  $procedure->set('NAME', $name);
  $procedure->set('ACTIVE', 'Y');
  $procedure->set(ClinicConfig::PROCEDURE_DESCRIPTION, $description);

  $result = $procedure->save();

  if (!$result->isSuccess()) {
   return [
    'success' => false,
    'id' => null,
    'errors' => $result->getErrorMessages(),
   ];
  }

  return [
   'success' => true,
   'id' => (int)$procedure->get('ID'),
   'errors' => [],
  ];
 }

 public function delete(int $id): array
 {
  $this->loadIblockModule();

  if ($id <= 0) {
   return [
    'success' => false,
    'errors' => ['Некорректный ID процедуры'],
   ];
  }

  $result = ElementProceduresTable::delete($id);

  if (!$result->isSuccess()) {
   return [
    'success' => false,
    'errors' => $result->getErrorMessages(),
   ];
  }

  return [
   'success' => true,
   'errors' => [],
  ];
 }

 private function getProcedureIblockId(): int
 {
  $row = IblockTable::getRow([
   'select' => ['ID'],
   'filter' => ['=CODE' => ClinicConfig::PROCEDURES_IBLOCK_CODE],
  ]);

  if (!$row) {
   throw new SystemException('Инфоблок procedures не найден');
  }

  return (int)$row['ID'];
 }

 private function loadIblockModule(): void
 {
  if (!Loader::includeModule('iblock')) {
   throw new SystemException('Модуль iblock не подключен');
  }
 }
}
