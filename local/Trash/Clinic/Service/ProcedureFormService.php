<?php

declare(strict_types=1);

namespace App\Clinic\Service;

use App\Clinic\Config\ClinicIblockCodes;
use App\Clinic\Config\ProcedureIblockFields;
use Bitrix\Iblock\Elements\ElementProceduresTable;
use Bitrix\Main\ORM\Objectify\EntityObject;

/**
 * Сервис сохранения процедуры через D7 ORM.
 *
 * Важно:
 * - save-path работает только через ORM;
 * - старые события инфоблоков OnBeforeIBlockElementAdd/Update для ORM не работают;
 * - поэтому генерация CODE выполняется здесь;
 * - запись идёт только через ORM-модель procedures.
 */
class ProcedureFormService extends AbstractIblockFormService
{
 /**
  * Возвращает символьный код инфоблока процедур.
  */
 protected function getIblockCode(): string
 {
  return ClinicIblockCodes::PROCEDURES;
 }

 /**
  * Используем явную ORM-модель инфоблока procedures.
  */
 protected function getDataClass(): string
 {
  $this->ensureIblockModuleLoaded();

  return ElementProceduresTable::class;
 }

 /**
  * Сохраняет процедуру.
  *
  * Ожидаемый входной массив:
  * [
  *   'name' => 'Первичный осмотр',
  *   'description' => 'Краткое описание процедуры',
  * ]
  *
  * Формат ответа:
  * [
  *   'success' => true|false,
  *   'id' => int|null,
  *   'errors' => string[],
  * ]
  *
  * @param array<string, mixed> $data
  * @return array{
  *   success: bool,
  *   id: int|null,
  *   errors: string[]
  * }
  */
 public function save(array $data): array
 {
  $name = trim((string)($data['name'] ?? ''));
  $description = trim((string)($data['description'] ?? ''));

  $errors = [];

  if ($name === '') {
   $errors[] = 'Не заполнено название процедуры.';
  }

  if ($errors !== []) {
   return [
    'success' => false,
    'id' => null,
    'errors' => $errors,
   ];
  }

  $procedure = $this->createElementObject();

  $this->assignIblockId($procedure);
  $procedure->set('NAME', $name);
  $procedure->set('CODE', $this->buildProcedureCode($name));
  $procedure->set('ACTIVE', 'Y');

  $createResult = $procedure->save();

  if (!$createResult->isSuccess()) {
   return [
    'success' => false,
    'id' => null,
    'errors' => $this->extractOrmErrors(
     $createResult,
     'Не удалось создать процедуру.'
    ),
   ];
  }

  $procedureId = $this->resolveSavedElementId($procedure, $createResult);

  if ($procedureId <= 0) {
   return [
    'success' => false,
    'id' => null,
    'errors' => ['Не удалось определить ID созданной процедуры.'],
   ];
  }

  if ($description !== '') {
   $procedure = $this->getProcedureObjectForSave($procedureId);

   if ($procedure === null) {
    ElementProceduresTable::delete($procedureId);

    return [
     'success' => false,
     'id' => null,
     'errors' => ['Созданная процедура не найдена для записи свойств.'],
    ];
   }

   $procedure->set(ProcedureIblockFields::DESCRIPTION, $description);

   $propertySaveResult = $procedure->save();

   if (!$propertySaveResult->isSuccess()) {
    ElementProceduresTable::delete($procedureId);

    return [
     'success' => false,
     'id' => null,
     'errors' => $this->extractOrmErrors(
      $propertySaveResult,
      'Не удалось сохранить описание процедуры.'
     ),
    ];
   }
  }

  return [
   'success' => true,
   'id' => $procedureId,
   'errors' => [],
  ];
 }

 /**
  * Загружает ORM-объект процедуры вместе со свойством описания.
  */
 private function getProcedureObjectForSave(int $procedureId): ?EntityObject
 {
  return $this->getElementObjectById($procedureId, [
   'ID',
   'IBLOCK_ID',
   'NAME',
   'CODE',
   ProcedureIblockFields::DESCRIPTION,
  ]);
 }

 /**
  * Назначает IBLOCK_ID новому ORM-объекту.
  */
 private function assignIblockId(EntityObject $procedure): void
 {
  $iblockId = $this->getIblockId();

  if (method_exists($procedure, 'setIblockId')) {
   $procedure->setIblockId($iblockId);

   return;
  }

  $procedure->set('IBLOCK_ID', $iblockId);
 }

 /**
  * Возвращает ID только что сохранённого ORM-объекта.
  */
 private function resolveSavedElementId(EntityObject $procedure, object $saveResult): int
 {
  $procedureId = (int)($procedure->get('ID') ?? 0);

  if ($procedureId > 0) {
   return $procedureId;
  }

  if (method_exists($saveResult, 'getId')) {
   return (int)$saveResult->getId();
  }

  return 0;
 }

 /**
  * Строит CODE процедуры по её названию.
  */
 private function buildProcedureCode(string $name): string
 {
  return $this->translit($this->normalizeText($name), 150);
 }

 /**
  * Транслитерирует строку.
  */
 private function translit(string $value, int $maxLen = 100): string
 {
  return (string)\CUtil::translit($value, 'ru', [
   'max_len' => $maxLen,
   'change_case' => 'L',
   'replace_space' => '-',
   'replace_other' => '-',
   'delete_repeat_replace' => 'true',
   'safe_chars' => '0123456789',
  ]);
 }

 /**
  * Нормализует текст.
  */
 private function normalizeText(string $value): string
 {
  $value = trim($value);
  $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

  return $value;
 }
}
