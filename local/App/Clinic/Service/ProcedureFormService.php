<?php

declare(strict_types=1);

namespace App\Clinic\Service;

use App\Clinic\Config\ClinicIblockCodes;
use App\Clinic\Config\ProcedureIblockFields;

/**
 * Сервис сохранения процедуры.
 *
 * Отвечает за:
 * - валидацию данных формы;
 * - создание элемента инфоблока процедур;
 * - сохранение свойств процедуры.
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
  */
 public function save(array $data): array
 {
  $name = trim((string)($data['name'] ?? ''));
  $description = trim((string)($data['description'] ?? ''));

  $errors = [];

  // Валидация обязательного названия процедуры.
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

  $elementFields = [
   'IBLOCK_ID' => $this->getIblockId(),
   'NAME' => $name,
   'ACTIVE' => 'Y',
  ];

  $elementApi = $this->getElementApi();
  $elementId = $elementApi->Add($elementFields);

  if (!$elementId) {
   return [
    'success' => false,
    'id' => null,
    'errors' => [
     $elementApi->LAST_ERROR ?: 'Не удалось создать процедуру.',
    ],
   ];
  }

  /**
   * Сохраняем свойства только после успешного Add().
   *
   * Важно:
   * этот код имеет смысл, только если в инфоблоке процедур
   * реально существует свойство с кодом PROCEDURE_DESCRIPTION.
   */
  $propertyValues = [];

  if ($description !== '') {
   $propertyValues[ProcedureIblockFields::DESCRIPTION] = $description;
  }

  if ($propertyValues !== []) {
   \CIBlockElement::SetPropertyValuesEx(
    (int)$elementId,
    $this->getIblockId(),
    $propertyValues
   );
  }

  return [
   'success' => true,
   'id' => (int)$elementId,
   'errors' => [],
  ];
 }
}
