<?php

declare(strict_types=1);

namespace App\Clinic\Repository;

use App\Clinic\Config\ClinicIblockCodes;
use App\Clinic\Config\DoctorIblockFields;
use App\Iblock\Repository\AbstractIblockRepository;

/**
 * Репозиторий врачей.
 */
class DoctorRepository extends AbstractIblockRepository
{
 public function getIblockCode(): string
 {
  return ClinicIblockCodes::DOCTORS;
 }

 /**
  * Для index.php.
  *
  * Возвращает именно такой контракт:
  * - ID
  * - LAST_NAME
  * - FIRST_NAME
  * - MIDDLE_NAME
  * - BIRTH_DATE
  * - INDIVIDUAL_TAX_NUMBER
  *
  * @return array<int, array<string, mixed>>
  */
 public function getDoctorList(): array
 {
  $rows = $this->getList([
   'select' => ['ID'],
   'filter' => ['=ACTIVE' => 'Y'],
   'order' => ['ID' => 'ASC'],
  ])->fetchAll();

  if (empty($rows)) {
   return [];
  }

  $result = [];

  foreach ($rows as $row) {
   $doctorId = (int)($row['ID'] ?? 0);

   if ($doctorId <= 0) {
    continue;
   }

   $lastName = $this->getSinglePropertyValue($doctorId, DoctorIblockFields::LAST_NAME);
   $firstName = $this->getSinglePropertyValue($doctorId, DoctorIblockFields::FIRST_NAME);
   $middleName = $this->getSinglePropertyValue($doctorId, DoctorIblockFields::MIDDLE_NAME);
   $birthDate = $this->getSinglePropertyValue($doctorId, DoctorIblockFields::BIRTH_DATE);
   $inn = $this->getSinglePropertyValue($doctorId, DoctorIblockFields::INDIVIDUAL_TAX_NUMBER);

   $result[] = [
    'ID' => $doctorId,
    'LAST_NAME' => $lastName ?? '',
    'FIRST_NAME' => $firstName ?? '',
    'MIDDLE_NAME' => $middleName ?? '',
    'BIRTH_DATE' => $birthDate ?? '',
    'INDIVIDUAL_TAX_NUMBER' => $inn ?? '',
   ];
  }

  return $result;
 }

 /**
  * Для doctor_form.php.
  *
  * @return array<string, mixed>
  */
 public function getDoctorForEdit(int $id): array
 {
  if (!$this->elementExists($id)) {
   return [];
  }

  $lastName = $this->getSinglePropertyValue($id, DoctorIblockFields::LAST_NAME);
  $firstName = $this->getSinglePropertyValue($id, DoctorIblockFields::FIRST_NAME);
  $middleName = $this->getSinglePropertyValue($id, DoctorIblockFields::MIDDLE_NAME);
  $birthDate = $this->getSinglePropertyValue($id, DoctorIblockFields::BIRTH_DATE);
  $inn = $this->getSinglePropertyValue($id, DoctorIblockFields::INDIVIDUAL_TAX_NUMBER);
  $procedureIds = $this->getMultipleIntPropertyValues($id, DoctorIblockFields::PROCEDURE_IDS);

  return [
   'id' => $id,
   'last_name' => $lastName,
   'first_name' => $firstName,
   'middle_name' => $middleName,
   'birth_date' => $birthDate,
   'individual_tax_number' => $inn,
   'procedure_ids' => $procedureIds,
  ];
 }

 /**
  * Для doctor_view.php.
  *
  * @return array<string, mixed>
  */
 public function getDoctorCardData(int $id): array
 {
  if (!$this->elementExists($id)) {
   return [];
  }

  $lastName = $this->getSinglePropertyValue($id, DoctorIblockFields::LAST_NAME);
  $firstName = $this->getSinglePropertyValue($id, DoctorIblockFields::FIRST_NAME);
  $middleName = $this->getSinglePropertyValue($id, DoctorIblockFields::MIDDLE_NAME);
  $birthDate = $this->getSinglePropertyValue($id, DoctorIblockFields::BIRTH_DATE);
  $inn = $this->getSinglePropertyValue($id, DoctorIblockFields::INDIVIDUAL_TAX_NUMBER);
  $procedureIds = $this->getMultipleIntPropertyValues($id, DoctorIblockFields::PROCEDURE_IDS);

  return [
   'id' => $id,
   'full_name' => $this->buildFullName($lastName, $firstName, $middleName),
   'last_name' => $lastName,
   'first_name' => $firstName,
   'middle_name' => $middleName,
   'birth_date' => $birthDate,
   'individual_tax_number' => $inn,
   'procedure_ids' => $procedureIds,
  ];
 }

 /**
  * Проверяет, существует ли элемент врача.
  */
 private function elementExists(int $id): bool
 {
  if ($id <= 0) {
   return false;
  }

  $row = $this->getList([
   'select' => ['ID'],
   'filter' => ['=ID' => $id],
   'limit' => 1,
  ])->fetch();

  return !empty($row['ID']);
 }

 /**
  * Читает одно значение свойства.
  */
 private function getSinglePropertyValue(int $elementId, string $propertyCode): ?string
 {
  $propertyResult = \CIBlockElement::GetProperty(
   $this->getIblockId(),
   $elementId,
   ['sort' => 'asc'],
   ['CODE' => $propertyCode]
  );

  $propertyRow = $propertyResult->Fetch();

  if (!$propertyRow) {
   return null;
  }

  $value = trim((string)($propertyRow['VALUE'] ?? ''));

  return $value !== '' ? $value : null;
 }

 /**
  * Читает множественное свойство как массив int ID.
  *
  * @return int[]
  */
 private function getMultipleIntPropertyValues(int $elementId, string $propertyCode): array
 {
  $propertyResult = \CIBlockElement::GetProperty(
   $this->getIblockId(),
   $elementId,
   ['sort' => 'asc'],
   ['CODE' => $propertyCode]
  );

  $result = [];

  while ($propertyRow = $propertyResult->Fetch()) {
   $value = (int)($propertyRow['VALUE'] ?? 0);

   if ($value > 0) {
    $result[] = $value;
   }
  }

  return array_values(array_unique($result));
 }

 /**
  * Собирает ФИО.
  */
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
