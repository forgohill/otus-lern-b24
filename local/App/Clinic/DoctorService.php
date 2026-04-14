<?php

declare(strict_types=1);

namespace App\Clinic;

use Bitrix\Iblock\Elements\ElementDoctorsTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\ORM\PropertyValue;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\SystemException;

class DoctorService
{
 public function create(array $data): array
 {
  $this->loadIblockModule();

  $doctorData = $this->extractDoctorData($data);
  $errors = $this->validateDoctorData($doctorData);

  if ($errors !== []) {
   return [
    'success' => false,
    'id' => null,
    'errors' => $errors,
   ];
  }

  $doctor = ElementDoctorsTable::createObject();

  $this->assignIblockId($doctor);
  $this->fillDoctorFields($doctor, $doctorData);

  $result = $doctor->save();

  if (!$result->isSuccess()) {
   return [
    'success' => false,
    'id' => null,
    'errors' => $this->extractOrmErrors($result, 'Не удалось создать врача'),
   ];
  }

  $doctorId = (int)$doctor->get('ID');

  if ($doctorData['procedure_ids'] !== []) {
   $syncResult = $this->syncDoctorProcedures($doctorId, $doctorData['procedure_ids']);

   if (!$syncResult['success']) {
    ElementDoctorsTable::delete($doctorId);

    return [
     'success' => false,
     'id' => null,
     'errors' => $syncResult['errors'],
    ];
   }
  }

  return [
   'success' => true,
   'id' => $doctorId,
   'errors' => [],
  ];
 }

 public function update(int $id, array $data): array
 {
  $this->loadIblockModule();

  if ($id <= 0) {
   return [
    'success' => false,
    'id' => null,
    'errors' => ['Некорректный ID врача'],
   ];
  }

  $doctorData = $this->extractDoctorData($data);
  $errors = $this->validateDoctorData($doctorData);

  if ($errors !== []) {
   return [
    'success' => false,
    'id' => null,
    'errors' => $errors,
   ];
  }

  $doctor = $this->loadDoctorForSave($id);

  if ($doctor === null) {
   return [
    'success' => false,
    'id' => null,
    'errors' => ['Врач не найден'],
   ];
  }

  $this->fillDoctorFields($doctor, $doctorData);
  $this->replaceMultiplePropertyValues(
   $doctor,
   ClinicConfig::DOCTOR_PROCEDURE_IDS,
   $doctorData['procedure_ids']
  );

  $result = $doctor->save();

  if (!$result->isSuccess()) {
   return [
    'success' => false,
    'id' => null,
    'errors' => $this->extractOrmErrors($result, 'Не удалось обновить врача'),
   ];
  }

  return [
   'success' => true,
   'id' => $id,
   'errors' => [],
  ];
 }

 private function extractDoctorData(array $data): array
 {
  return [
   'last_name' => trim((string)($data['last_name'] ?? '')),
   'first_name' => trim((string)($data['first_name'] ?? '')),
   'middle_name' => trim((string)($data['middle_name'] ?? '')),
   'procedure_ids' => $this->normalizeProcedureIds($data['procedure_ids'] ?? []),
  ];
 }

 private function validateDoctorData(array $doctorData): array
 {
  $errors = [];

  if ($doctorData['last_name'] === '') {
   $errors[] = 'Не заполнена фамилия врача';
  }

  if ($doctorData['first_name'] === '') {
   $errors[] = 'Не заполнено имя врача';
  }

  return $errors;
 }

 private function fillDoctorFields(EntityObject $doctor, array $doctorData): void
 {
  $doctor->set(
   'NAME',
   $this->buildName(
    $doctorData['last_name'],
    $doctorData['first_name'],
    $doctorData['middle_name']
   )
  );
  $doctor->set('ACTIVE', 'Y');
  $doctor->set(ClinicConfig::DOCTOR_LAST_NAME, $doctorData['last_name']);
  $doctor->set(ClinicConfig::DOCTOR_FIRST_NAME, $doctorData['first_name']);
  $doctor->set(
   ClinicConfig::DOCTOR_MIDDLE_NAME,
   $doctorData['middle_name'] !== '' ? $doctorData['middle_name'] : null
  );
 }

 private function buildName(
  string $lastName,
  string $firstName,
  string $middleName
 ): string {
  $fullName = trim(implode(' ', array_filter([
   $lastName,
   $firstName,
   $middleName,
  ])));

  return (string)\CUtil::translit($fullName, 'ru', [
   'max_len' => 255,
   'change_case' => 'L',
   'replace_space' => '-',
   'replace_other' => '-',
   'delete_repeat_replace' => 'true',
   'safe_chars' => '',
  ]);
 }

 private function syncDoctorProcedures(int $doctorId, array $procedureIds): array
 {
  $doctor = $this->loadDoctorForSave($doctorId);

  if ($doctor === null) {
   return [
    'success' => false,
    'errors' => ['Не удалось загрузить врача для сохранения процедур'],
   ];
  }

  $this->replaceMultiplePropertyValues(
   $doctor,
   ClinicConfig::DOCTOR_PROCEDURE_IDS,
   $procedureIds
  );

  $result = $doctor->save();

  if (!$result->isSuccess()) {
   return [
    'success' => false,
    'errors' => $this->extractOrmErrors(
     $result,
     'Не удалось сохранить процедуры врача'
    ),
   ];
  }

  return [
   'success' => true,
   'errors' => [],
  ];
 }

 private function loadDoctorForSave(int $doctorId): ?EntityObject
 {
  return ElementDoctorsTable::getByPrimary($doctorId, [
   'select' => [
    'ID',
    'NAME',
    'ACTIVE',
    ClinicConfig::DOCTOR_LAST_NAME,
    ClinicConfig::DOCTOR_FIRST_NAME,
    ClinicConfig::DOCTOR_MIDDLE_NAME,
    ClinicConfig::DOCTOR_PROCEDURE_IDS,
   ],
  ])->fetchObject();
 }

 private function replaceMultiplePropertyValues(
  EntityObject $doctor,
  string $propertyCode,
  array $values
 ): void {
  $doctor->removeAll($propertyCode);

  foreach ($values as $value) {
   $doctor->addTo($propertyCode, new PropertyValue($value));
  }
 }

 private function normalizeProcedureIds(mixed $value): array
 {
  if (!is_array($value)) {
   return [];
  }

  $result = [];

  foreach ($value as $procedureId) {
   $procedureId = (int)$procedureId;

   if ($procedureId > 0) {
    $result[] = $procedureId;
   }
  }

  return array_values(array_unique($result));
 }

 private function assignIblockId(object $doctor): void
 {
  $iblockId = $this->getDoctorsIblockId();

  if (method_exists($doctor, 'setIblockId')) {
   $doctor->setIblockId($iblockId);

   return;
  }

  $doctor->set('IBLOCK_ID', $iblockId);
 }

 private function getDoctorsIblockId(): int
 {
  $row = IblockTable::getRow([
   'select' => ['ID'],
   'filter' => ['=CODE' => ClinicConfig::IBLOCK_DOCTORS],
  ]);

  if (!$row) {
   throw new SystemException('Инфоблок doctors не найден');
  }

  return (int)$row['ID'];
 }

 private function extractOrmErrors(Result $result, string $defaultMessage): array
 {
  $errors = array_values(array_filter(
   array_map('trim', $result->getErrorMessages()),
   static fn(string $error): bool => $error !== ''
  ));

  return $errors !== [] ? $errors : [$defaultMessage];
 }

 private function loadIblockModule(): void
 {
  if (!Loader::includeModule('iblock')) {
   throw new SystemException('Модуль iblock не подключен');
  }
 }
}
