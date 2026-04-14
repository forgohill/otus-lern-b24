<?php

declare(strict_types=1);

namespace App\Clinic;

use Bitrix\Iblock\Elements\ElementDoctorsTable;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\SystemException;

class DoctorRepository
{
 public function getList(): array
 {
  $this->loadIblockModule();

  $rows = ElementDoctorsTable::getList([
   'select' => [
    'ID',
    'LAST_NAME_VALUE' => $this->buildPropertyValueSelect(ClinicConfig::DOCTOR_LAST_NAME),
    'FIRST_NAME_VALUE' => $this->buildPropertyValueSelect(ClinicConfig::DOCTOR_FIRST_NAME),
    'MIDDLE_NAME_VALUE' => $this->buildPropertyValueSelect(ClinicConfig::DOCTOR_MIDDLE_NAME),
   ],
   'filter' => [
    '=ACTIVE' => 'Y',
   ],
   'order' => [
    'ID' => 'ASC',
   ],
  ])->fetchAll();

  $result = [];

  foreach ($rows as $row) {
   $lastName = trim((string)($row['LAST_NAME_VALUE'] ?? ''));
   $firstName = trim((string)($row['FIRST_NAME_VALUE'] ?? ''));
   $middleName = trim((string)($row['MIDDLE_NAME_VALUE'] ?? ''));

   $result[] = [
    'ID' => (int)($row['ID'] ?? 0),
    'LAST_NAME' => $lastName,
    'FIRST_NAME' => $firstName,
    'MIDDLE_NAME' => $middleName,
    'FULL_NAME' => $this->buildFullName($lastName, $firstName, $middleName),
   ];
  }

  return $result;
 }

 public function getById(int $id): ?array
 {
  $doctor = $this->getDoctorObject($id);

  if ($doctor === null) {
   return null;
  }

  $lastName = $this->extractSinglePropertyValue(
   $doctor->get(ClinicConfig::DOCTOR_LAST_NAME)
  ) ?? '';

  $firstName = $this->extractSinglePropertyValue(
   $doctor->get(ClinicConfig::DOCTOR_FIRST_NAME)
  ) ?? '';

  $middleName = $this->extractSinglePropertyValue(
   $doctor->get(ClinicConfig::DOCTOR_MIDDLE_NAME)
  ) ?? '';

  return [
   'ID' => (int)$doctor->get('ID'),
   'LAST_NAME' => $lastName,
   'FIRST_NAME' => $firstName,
   'MIDDLE_NAME' => $middleName,
   'FULL_NAME' => $this->buildFullName($lastName, $firstName, $middleName),
   'PROCEDURE_IDS' => $this->extractMultipleIntPropertyValues(
    $doctor->get(ClinicConfig::DOCTOR_PROCEDURE_IDS)
   ),
  ];
 }

 public function getCardData(int $id): ?array
 {
  $doctor = $this->getById($id);

  if ($doctor === null) {
   return null;
  }

  return [
   'id' => (int)$doctor['ID'],
   'full_name' => (string)$doctor['FULL_NAME'],
   'last_name' => (string)$doctor['LAST_NAME'],
   'first_name' => (string)$doctor['FIRST_NAME'],
   'middle_name' => (string)$doctor['MIDDLE_NAME'],
   'procedure_ids' => is_array($doctor['PROCEDURE_IDS'] ?? null)
    ? $doctor['PROCEDURE_IDS']
    : [],
  ];
 }

 private function getDoctorObject(int $id): ?EntityObject
 {
  if ($id <= 0) {
   return null;
  }

  $this->loadIblockModule();

  return ElementDoctorsTable::getByPrimary($id, [
   'select' => [
    'ID',
    $this->buildPropertyValueSelect(ClinicConfig::DOCTOR_LAST_NAME),
    $this->buildPropertyValueSelect(ClinicConfig::DOCTOR_FIRST_NAME),
    $this->buildPropertyValueSelect(ClinicConfig::DOCTOR_MIDDLE_NAME),
    $this->buildPropertyValueSelect(ClinicConfig::DOCTOR_PROCEDURE_IDS),
   ],
  ])->fetchObject();
 }

 private function loadIblockModule(): void
 {
  if (!Loader::includeModule('iblock')) {
   throw new SystemException('Модуль iblock не подключен');
  }
 }

 private function buildPropertyValueSelect(string $propertyCode): string
 {
  return $propertyCode . '.VALUE';
 }

 private function extractSinglePropertyValue(mixed $propertyValue): ?string
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

 private function extractMultipleIntPropertyValues(mixed $propertyValue): array
 {
  if ($propertyValue === null) {
   return [];
  }

  $result = [];

  if (is_scalar($propertyValue)) {
   $this->appendIntValue($result, $propertyValue);

   return array_values(array_unique($result));
  }

  if (is_object($propertyValue) && method_exists($propertyValue, 'getAll')) {
   $propertyValue = $propertyValue->getAll();
  }

  if (is_iterable($propertyValue)) {
   foreach ($propertyValue as $item) {
    $this->appendIntValue($result, $item);
   }
  } else {
   $this->appendIntValue($result, $propertyValue);
  }

  return array_values(array_unique($result));
 }

 private function appendIntValue(array &$result, mixed $value): void
 {
  if (is_scalar($value)) {
   $intValue = (int)$value;

   if ($intValue > 0) {
    $result[] = $intValue;
   }

   return;
  }

  if (!is_object($value)) {
   return;
  }

  $rawValue = null;

  if (method_exists($value, 'getValue')) {
   $rawValue = $value->getValue();
  } elseif (method_exists($value, 'get')) {
   $rawValue = $value->get('VALUE');
  }

  $intValue = (int)$rawValue;

  if ($intValue > 0) {
   $result[] = $intValue;
  }
 }

 private function normalizeNullableString(mixed $value): ?string
 {
  if ($value === null) {
   return null;
  }

  $value = trim((string)$value);

  return $value !== '' ? $value : null;
 }

 private function buildFullName(
  string $lastName,
  string $firstName,
  string $middleName
 ): string {
  return trim(implode(' ', array_filter([
   $lastName,
   $firstName,
   $middleName,
  ])));
 }
}
