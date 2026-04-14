<?php

declare(strict_types=1);

namespace App\Clinic\Service;

use App\Clinic\Config\ClinicIblockCodes;
use App\Clinic\Config\DoctorIblockFields;

/**
 * Сервис сохранения врача.
 *
 * Отвечает за:
 * - валидацию данных формы врача;
 * - создание нового элемента инфоблока doctors;
 * - обновление существующего элемента;
 * - сохранение бизнес-свойств врача;
 * - сохранение множественной связи "врач -> процедуры".
 */
class DoctorFormService extends AbstractIblockFormService
{
 /**
  * Возвращает символьный код инфоблока врачей.
  */
 protected function getIblockCode(): string
 {
  return ClinicIblockCodes::DOCTORS;
 }

 /**
  * Сохраняет врача.
  *
  * Если $id === null -> создаём нового врача.
  * Если $id !== null -> обновляем существующего врача.
  *
  * @param array<string, mixed> $data
  * @param int|null $id
  *
  * @return array{
  *     success: bool,
  *     id: int|null,
  *     errors: string[]
  * }
  */
 public function save(array $data, ?int $id = null): array
 {
  $lastName = $this->readString($data, ['LAST_NAME', 'last_name']);
  $firstName = $this->readString($data, ['FIRST_NAME', 'first_name']);
  $middleName = $this->readString($data, ['MIDDLE_NAME', 'middle_name']);

  $inn = $this->readString($data, ['INN', 'individual_tax_number']);
  $birthDateRaw = $this->readNullableString($data, ['BIRTH_DATE', 'birth_date']);

  $procedureIds = $this->normalizeProcedureIds(
   $data['PROCEDURES'] ?? $data['procedure_ids'] ?? []
  );

  $errors = [];

  if ($lastName === '') {
   $errors[] = 'Не заполнена фамилия врача.';
  }

  if ($firstName === '') {
   $errors[] = 'Не заполнено имя врача.';
  }

  if ($inn === '') {
   $errors[] = 'Не заполнен ИНН врача.';
  } elseif (!preg_match('/^[1-9][0-9]{11}$/', $inn)) {
   $errors[] = 'ИНН должен состоять из 12 цифр и не может начинаться с 0.';
  }

  $birthDate = null;
  if ($birthDateRaw !== null && $birthDateRaw !== '') {
   $birthDate = $this->normalizeBirthDate($birthDateRaw);

   if ($birthDate === null) {
    $errors[] = 'Некорректный формат даты рождения.';
   }
  }

  if ($errors !== []) {
   return [
    'success' => false,
    'id' => null,
    'errors' => $errors,
   ];
  }

  /**
   * Технический NAME собираем из ФИО.
   */
  $technicalName = $this->buildTechnicalName($lastName, $firstName, $middleName);

  /**
   * Свойства врача собираем один раз.
   */
  $propertyValues = [
   DoctorIblockFields::LAST_NAME => $lastName,
   DoctorIblockFields::FIRST_NAME => $firstName,
   DoctorIblockFields::MIDDLE_NAME => ($middleName !== '' ? $middleName : false),
   DoctorIblockFields::INDIVIDUAL_TAX_NUMBER => $inn,
   DoctorIblockFields::PROCEDURE_IDS => ($procedureIds !== [] ? $procedureIds : false),
  ];

  if ($this->hasAnyKey($data, ['BIRTH_DATE', 'birth_date'])) {
   $propertyValues[DoctorIblockFields::BIRTH_DATE] = $birthDate ?: false;
  }

  $elementApi = $this->getElementApi();
  $iblockId = $this->getIblockId();

  /**
   * CREATE
   *
   * Для нового элемента свойства передаём сразу в Add()
   * через PROPERTY_VALUES, чтобы они были доступны
   * уже на этапе OnBeforeIBlockElementAdd.
   */
  if ($id === null) {
   $elementFields = [
    'IBLOCK_ID' => $iblockId,
    'NAME' => $technicalName,
    'ACTIVE' => 'Y',
    'PROPERTY_VALUES' => $propertyValues,
   ];

   $elementId = $elementApi->Add($elementFields);

   if (!$elementId) {
    return [
     'success' => false,
     'id' => null,
     'errors' => [
      $elementApi->LAST_ERROR ?: 'Не удалось создать врача.',
     ],
    ];
   }

   return [
    'success' => true,
    'id' => (int) $elementId,
    'errors' => [],
   ];
  }

  /**
   * UPDATE
   *
   * Для существующего элемента сначала обновляем поля,
   * потом отдельно обновляем свойства.
   */
  $elementId = (int) $id;

  $elementFields = [
   'NAME' => $technicalName,
  ];

  $updateResult = $elementApi->Update($elementId, $elementFields);

  if (!$updateResult) {
   return [
    'success' => false,
    'id' => null,
    'errors' => [
     $elementApi->LAST_ERROR ?: 'Не удалось обновить врача.',
    ],
   ];
  }

  \CIBlockElement::SetPropertyValuesEx(
   $elementId,
   $iblockId,
   $propertyValues
  );

  return [
   'success' => true,
   'id' => $elementId,
   'errors' => [],
  ];
 }

 /**
  * Собирает технический NAME из ФИО.
  */
 private function buildTechnicalName(
  string $lastName,
  string $firstName,
  string $middleName
 ): string {
  $parts = array_filter([
   trim($lastName),
   trim($firstName),
   trim($middleName),
  ], static fn(string $value): bool => $value !== '');

  return implode(' ', $parts);
 }

 /**
  * Приводит дату рождения к формату d.m.Y.
  */
 private function normalizeBirthDate(string $value): ?string
 {
  $value = trim($value);

  if ($value === '') {
   return null;
  }

  if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
   $date = \DateTime::createFromFormat('Y-m-d', $value);

   return $date instanceof \DateTime ? $date->format('d.m.Y') : null;
  }

  if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $value) === 1) {
   $date = \DateTime::createFromFormat('d.m.Y', $value);

   return $date instanceof \DateTime ? $date->format('d.m.Y') : null;
  }

  return null;
 }

 /**
  * Нормализует список ID процедур.
  *
  * @param mixed $value
  * @return int[]
  */
 private function normalizeProcedureIds(mixed $value): array
 {
  if (!is_array($value)) {
   return [];
  }

  $result = [];

  foreach ($value as $procedureId) {
   $procedureId = (int) $procedureId;

   if ($procedureId > 0) {
    $result[] = $procedureId;
   }
  }

  return array_values(array_unique($result));
 }

 /**
  * Читает первое найденное строковое значение по списку ключей.
  *
  * @param array<string, mixed> $data
  * @param string[] $keys
  */
 private function readString(array $data, array $keys): string
 {
  foreach ($keys as $key) {
   if (array_key_exists($key, $data)) {
    return trim((string) $data[$key]);
   }
  }

  return '';
 }

 /**
  * Читает nullable-строку по списку ключей.
  *
  * @param array<string, mixed> $data
  * @param string[] $keys
  */
 private function readNullableString(array $data, array $keys): ?string
 {
  foreach ($keys as $key) {
   if (array_key_exists($key, $data)) {
    return trim((string) $data[$key]);
   }
  }

  return null;
 }

 /**
  * Проверяет, пришёл ли хотя бы один из ключей в массиве данных.
  *
  * @param array<string, mixed> $data
  * @param string[] $keys
  */
 private function hasAnyKey(array $data, array $keys): bool
 {
  foreach ($keys as $key) {
   if (array_key_exists($key, $data)) {
    return true;
   }
  }

  return false;
 }
}
