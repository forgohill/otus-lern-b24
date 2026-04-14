<?php

declare(strict_types=1);

namespace App\Clinic\Repository;

use App\Clinic\Config\DoctorIblockFields;
use Bitrix\Iblock\Elements\ElementDoctorsTable;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\SystemException;

/**
 * Репозиторий врачей.
 *
 * Чтение выполняется через D7 ORM-класс инфоблока doctors:
 * \Bitrix\Iblock\Elements\ElementDoctorsTable
 *
 * Важно:
 * для работы этого класса у инфоблока doctors должен быть выставлен API_CODE = Doctors.
 */
class DoctorRepository
{
 public function getDoctorList(): array
 {
  $this->ensureIblockModuleLoaded();

  $collection = ElementDoctorsTable::getList([
   'select' => [
    'ID',
    $this->buildPropertyValueSelect(DoctorIblockFields::LAST_NAME),
    $this->buildPropertyValueSelect(DoctorIblockFields::FIRST_NAME),
    $this->buildPropertyValueSelect(DoctorIblockFields::MIDDLE_NAME),
    $this->buildPropertyValueSelect(DoctorIblockFields::BIRTH_DATE),
    $this->buildPropertyValueSelect(DoctorIblockFields::INDIVIDUAL_TAX_NUMBER),
   ],
   'filter' => ['=ACTIVE' => 'Y'],
   'order' => ['ID' => 'ASC'],
  ])->fetchCollection();

  if ($collection === null) {
   return [];
  }

  $result = [];

  foreach ($collection as $doctor) {
   $result[] = [
    'ID' => (int)$doctor->get('ID'),
    'LAST_NAME' => $this->extractSinglePropertyValue(
     $doctor->get(DoctorIblockFields::LAST_NAME)
    ) ?? '',
    'FIRST_NAME' => $this->extractSinglePropertyValue(
     $doctor->get(DoctorIblockFields::FIRST_NAME)
    ) ?? '',
    'MIDDLE_NAME' => $this->extractSinglePropertyValue(
     $doctor->get(DoctorIblockFields::MIDDLE_NAME)
    ) ?? '',
    'BIRTH_DATE' => $this->extractSinglePropertyValue(
     $doctor->get(DoctorIblockFields::BIRTH_DATE)
    ) ?? '',
    'INDIVIDUAL_TAX_NUMBER' => $this->extractSinglePropertyValue(
     $doctor->get(DoctorIblockFields::INDIVIDUAL_TAX_NUMBER)
    ) ?? '',
   ];
  }

  return $result;
 }

 public function getDoctorForEdit(int $id): array
 {
  $doctor = $this->getDoctorObject($id);

  if ($doctor === null) {
   return [];
  }

  return [
   'id' => (int)$doctor->get('ID'),
   'last_name' => $this->extractSinglePropertyValue(
    $doctor->get(DoctorIblockFields::LAST_NAME)
   ),
   'first_name' => $this->extractSinglePropertyValue(
    $doctor->get(DoctorIblockFields::FIRST_NAME)
   ),
   'middle_name' => $this->extractSinglePropertyValue(
    $doctor->get(DoctorIblockFields::MIDDLE_NAME)
   ),
   'birth_date' => $this->extractSinglePropertyValue(
    $doctor->get(DoctorIblockFields::BIRTH_DATE)
   ),
   'individual_tax_number' => $this->extractSinglePropertyValue(
    $doctor->get(DoctorIblockFields::INDIVIDUAL_TAX_NUMBER)
   ),
   'procedure_ids' => $this->extractMultipleIntPropertyValues(
    $doctor->get(DoctorIblockFields::PROCEDURE_IDS)
   ),
  ];
 }

 public function getDoctorCardData(int $id): array
 {
  $doctor = $this->getDoctorObject($id);

  if ($doctor === null) {
   return [];
  }

  $lastName = $this->extractSinglePropertyValue(
   $doctor->get(DoctorIblockFields::LAST_NAME)
  );
  $firstName = $this->extractSinglePropertyValue(
   $doctor->get(DoctorIblockFields::FIRST_NAME)
  );
  $middleName = $this->extractSinglePropertyValue(
   $doctor->get(DoctorIblockFields::MIDDLE_NAME)
  );

  return [
   'id' => (int)$doctor->get('ID'),
   'full_name' => $this->buildFullName($lastName, $firstName, $middleName),
   'last_name' => $lastName,
   'first_name' => $firstName,
   'middle_name' => $middleName,
   'birth_date' => $this->extractSinglePropertyValue(
    $doctor->get(DoctorIblockFields::BIRTH_DATE)
   ),
   'individual_tax_number' => $this->extractSinglePropertyValue(
    $doctor->get(DoctorIblockFields::INDIVIDUAL_TAX_NUMBER)
   ),
   'procedure_ids' => $this->extractMultipleIntPropertyValues(
    $doctor->get(DoctorIblockFields::PROCEDURE_IDS)
   ),
  ];
 }

 private function getDoctorObject(int $id): ?EntityObject
 {
  if ($id <= 0) {
   return null;
  }

  $this->ensureIblockModuleLoaded();

  return ElementDoctorsTable::getByPrimary($id, [
   'select' => [
    'ID',
    $this->buildPropertyValueSelect(DoctorIblockFields::LAST_NAME),
    $this->buildPropertyValueSelect(DoctorIblockFields::FIRST_NAME),
    $this->buildPropertyValueSelect(DoctorIblockFields::MIDDLE_NAME),
    $this->buildPropertyValueSelect(DoctorIblockFields::BIRTH_DATE),
    $this->buildPropertyValueSelect(DoctorIblockFields::INDIVIDUAL_TAX_NUMBER),
    $this->buildPropertyValueSelect(DoctorIblockFields::PROCEDURE_IDS),
   ],
  ])->fetchObject();
 }

 private function ensureIblockModuleLoaded(): void
 {
  if (!Loader::includeModule('iblock')) {
   throw new SystemException('Модуль iblock не подключен.');
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
  ?string $lastName,
  ?string $firstName,
  ?string $middleName
 ): string {
  $parts = array_filter([
   $lastName,
   $firstName,
   $middleName,
  ], static fn(?string $value): bool => $value !== null && $value !== '');

  return implode(' ', $parts);
 }
}
