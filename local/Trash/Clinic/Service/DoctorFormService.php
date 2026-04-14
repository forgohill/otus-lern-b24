<?php

declare(strict_types=1);

namespace App\Clinic\Service;

use App\Clinic\Config\ClinicIblockCodes;
use App\Clinic\Config\DoctorIblockFields;
use Bitrix\Iblock\Elements\ElementDoctorsTable;
use Bitrix\Iblock\ORM\PropertyValue;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\SystemException;

/**
 * Сервис сохранения врача через D7 ORM.
 *
 * Важно:
 * - save-path работает только через ORM;
 * - старые события инфоблоков OnBeforeIBlockElementAdd/Update для ORM не работают;
 * - поэтому валидация ИНН, проверка дублей и генерация NAME/CODE выполняются здесь;
 * - множественное свойство процедур записывается через relation-методы ORM.
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
  * Используем явную ORM-модель инфоблока doctors.
  */
 protected function getDataClass(): string
 {
  $this->ensureIblockModuleLoaded();

  return ElementDoctorsTable::class;
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

  $innRaw = $this->readString($data, ['INN', 'individual_tax_number']);
  $inn = $this->normalizeInn($innRaw);

  $birthDateRaw = $this->readNullableString($data, ['BIRTH_DATE', 'birth_date']);
  $touchBirthDate = $this->hasAnyKey($data, ['BIRTH_DATE', 'birth_date']);

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
  if ($touchBirthDate && $birthDateRaw !== null && $birthDateRaw !== '') {
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

  $duplicateDoctorId = $this->findDuplicateDoctorIdByInn($inn, $id);

  if ($duplicateDoctorId !== null) {
   return [
    'success' => false,
    'id' => null,
    'errors' => [
     'Элемент врача с таким ИНН уже существует. ID дубля: ' . $duplicateDoctorId,
    ],
   ];
  }

  $generatedCode = $this->buildDoctorCode($inn);
  $generatedName = $this->buildDoctorName($lastName, $inn);

  if ($id === null) {
   return $this->createDoctor(
    $generatedName,
    $generatedCode,
    $lastName,
    $firstName,
    $middleName,
    $birthDate,
    $touchBirthDate,
    $inn,
    $procedureIds
   );
  }

  return $this->updateDoctor(
   (int)$id,
   $generatedName,
   $generatedCode,
   $lastName,
   $firstName,
   $middleName,
   $birthDate,
   $touchBirthDate,
   $inn,
   $procedureIds
  );
 }

 /**
  * Создаёт нового врача через ORM.
  *
  * Логика двухшаговая:
  * 1. Первый save() создаёт элемент с обязательными одиночными полями/свойствами.
  * 2. После появления ID вторым save() записывается множественное relation-свойство процедур.
  *
  * @param int[] $procedureIds
  * @return array{success: bool, id: int|null, errors: string[]}
  */
 private function createDoctor(
  string $generatedName,
  string $generatedCode,
  string $lastName,
  string $firstName,
  string $middleName,
  ?string $birthDate,
  bool $touchBirthDate,
  string $inn,
  array $procedureIds
 ): array {
  try {
   $doctor = $this->createElementObject();

   $this->assignIblockId($doctor);
   $doctor->set('NAME', $generatedName);
   $doctor->set('CODE', $generatedCode);
   $doctor->set('ACTIVE', 'Y');

   $this->fillDoctorScalarFields(
    $doctor,
    $lastName,
    $firstName,
    $middleName,
    $birthDate,
    $touchBirthDate,
    $inn
   );

   $createResult = $doctor->save();

   if (!$createResult->isSuccess()) {
    return [
     'success' => false,
     'id' => null,
     'errors' => $this->extractOrmErrors(
      $createResult,
      'Не удалось создать врача.'
     ),
    ];
   }

   $doctorId = $this->resolveSavedElementId($doctor, $createResult);

   if ($doctorId <= 0) {
    return [
     'success' => false,
     'id' => null,
     'errors' => ['Не удалось определить ID созданного врача.'],
    ];
   }

   if ($procedureIds !== []) {
    $doctorForProcedures = $this->getDoctorObjectForSave($doctorId);

    if ($doctorForProcedures === null) {
     $this->deleteDoctorSilently($doctorId);

     return [
      'success' => false,
      'id' => null,
      'errors' => ['Созданный врач не найден для записи процедур.'],
     ];
    }

    $this->replaceMultiplePropertyValues(
     $doctorForProcedures,
     DoctorIblockFields::PROCEDURE_IDS,
     $procedureIds
    );

    $relationSaveResult = $doctorForProcedures->save();

    if (!$relationSaveResult->isSuccess()) {
     $this->deleteDoctorSilently($doctorId);

     return [
      'success' => false,
      'id' => null,
      'errors' => $this->extractOrmErrors(
       $relationSaveResult,
       'Не удалось сохранить процедуры врача.'
      ),
     ];
    }
   }

   return [
    'success' => true,
    'id' => $doctorId,
    'errors' => [],
   ];
  } catch (\Throwable $e) {
   return [
    'success' => false,
    'id' => null,
    'errors' => ['Ошибка при сохранении врача: ' . $e->getMessage()],
   ];
  }
 }

 /**
  * Обновляет существующего врача через ORM.
  *
  * У существующего врача ID уже есть, поэтому scalar-поля и relation-свойство
  * процедур можно подготовить на объекте до одного save().
  *
  * @param int[] $procedureIds
  * @return array{success: bool, id: int|null, errors: string[]}
  */
 private function updateDoctor(
  int $doctorId,
  string $generatedName,
  string $generatedCode,
  string $lastName,
  string $firstName,
  string $middleName,
  ?string $birthDate,
  bool $touchBirthDate,
  string $inn,
  array $procedureIds
 ): array {
  try {
   $doctor = $this->getDoctorObjectForSave($doctorId);

   if ($doctor === null) {
    return [
     'success' => false,
     'id' => null,
     'errors' => ['Врач не найден.'],
    ];
   }

   $doctor->set('NAME', $generatedName);
   $doctor->set('CODE', $generatedCode);

   $this->fillDoctorScalarFields(
    $doctor,
    $lastName,
    $firstName,
    $middleName,
    $birthDate,
    $touchBirthDate,
    $inn
   );

   $this->replaceMultiplePropertyValues(
    $doctor,
    DoctorIblockFields::PROCEDURE_IDS,
    $procedureIds
   );

   $saveResult = $doctor->save();

   if (!$saveResult->isSuccess()) {
    return [
     'success' => false,
     'id' => null,
     'errors' => $this->extractOrmErrors(
      $saveResult,
      'Не удалось обновить врача.'
     ),
    ];
   }

   return [
    'success' => true,
    'id' => $doctorId,
    'errors' => [],
   ];
  } catch (\Throwable $e) {
   return [
    'success' => false,
    'id' => null,
    'errors' => ['Ошибка при сохранении врача: ' . $e->getMessage()],
   ];
  }
 }

 /**
  * Загружает ORM-объект врача вместе с полями/свойствами,
  * которые участвуют в сохранении.
  */
 private function getDoctorObjectForSave(int $doctorId): ?EntityObject
 {
  return $this->getElementObjectById($doctorId, [
   'ID',
   'IBLOCK_ID',
   'NAME',
   'CODE',
   DoctorIblockFields::LAST_NAME,
   DoctorIblockFields::FIRST_NAME,
   DoctorIblockFields::MIDDLE_NAME,
   DoctorIblockFields::BIRTH_DATE,
   DoctorIblockFields::INDIVIDUAL_TAX_NUMBER,
   DoctorIblockFields::PROCEDURE_IDS,
  ]);
 }

 /**
  * Заполняет одиночные бизнес-поля и свойства врача.
  */
 private function fillDoctorScalarFields(
  EntityObject $doctor,
  string $lastName,
  string $firstName,
  string $middleName,
  ?string $birthDate,
  bool $touchBirthDate,
  string $inn
 ): void {
  $doctor->set(DoctorIblockFields::LAST_NAME, $lastName);
  $doctor->set(DoctorIblockFields::FIRST_NAME, $firstName);
  $doctor->set(
   DoctorIblockFields::MIDDLE_NAME,
   $middleName !== '' ? $middleName : null
  );
  $doctor->set(DoctorIblockFields::INDIVIDUAL_TAX_NUMBER, $inn);

  if ($touchBirthDate) {
   $doctor->set(
    DoctorIblockFields::BIRTH_DATE,
    ($birthDate !== null && $birthDate !== '') ? $birthDate : null
   );
  }
 }

 /**
  * Полностью заменяет значения множественного свойства через relation-методы ORM.
  *
  * @param int[] $values
  * @throws SystemException
  */
 private function replaceMultiplePropertyValues(
  EntityObject $object,
  string $propertyCode,
  array $values
 ): void {
  $methodSuffix = $this->buildFieldMethodSuffix($propertyCode);
  $removeAllMethod = 'removeAll' . $methodSuffix;
  $addToMethod = 'addTo' . $methodSuffix;

  if (!is_callable([$object, $removeAllMethod]) || !is_callable([$object, $addToMethod])) {
   throw new SystemException(
    sprintf(
     'Для множественного свойства "%s" недоступны ORM relation-методы %s() / %s().',
     $propertyCode,
     $removeAllMethod,
     $addToMethod
    )
   );
  }

  $object->{$removeAllMethod}();

  foreach ($values as $value) {
   $object->{$addToMethod}(new PropertyValue($value));
  }
 }

 /**
  * Назначает IBLOCK_ID новому ORM-объекту.
  */
 private function assignIblockId(EntityObject $doctor): void
 {
  $iblockId = $this->getIblockId();

  if (method_exists($doctor, 'setIblockId')) {
   $doctor->setIblockId($iblockId);

   return;
  }

  $doctor->set('IBLOCK_ID', $iblockId);
 }

 /**
  * Возвращает ID только что сохранённого ORM-объекта.
  */
 private function resolveSavedElementId(EntityObject $doctor, object $saveResult): int
 {
  $doctorId = (int)($doctor->get('ID') ?? 0);

  if ($doctorId > 0) {
   return $doctorId;
  }

  if (method_exists($saveResult, 'getId')) {
   return (int)$saveResult->getId();
  }

  return 0;
 }

 /**
  * Тихо удаляет только что созданного врача, если вторая стадия create не удалась.
  */
 private function deleteDoctorSilently(int $doctorId): void
 {
  if ($doctorId <= 0) {
   return;
  }

  try {
   ElementDoctorsTable::delete($doctorId);
  } catch (\Throwable) {
  }
 }

 /**
  * Ищет дубль врача по ИНН.
  */
 private function findDuplicateDoctorIdByInn(string $inn, ?int $excludeId = null): ?int
 {
  $this->ensureIblockModuleLoaded();

  $filter = [
   '=' . DoctorIblockFields::INDIVIDUAL_TAX_NUMBER . '.VALUE' => $inn,
  ];

  if ($excludeId !== null && $excludeId > 0) {
   $filter['!ID'] = $excludeId;
  }

  $row = ElementDoctorsTable::getList([
   'select' => ['ID'],
   'filter' => $filter,
   'limit' => 1,
  ])->fetch();

  if (!$row || empty($row['ID'])) {
   return null;
  }

  return (int)$row['ID'];
 }

 /**
  * Строит CODE врача из ИНН.
  */
 private function buildDoctorCode(string $inn): string
 {
  return $this->normalizeInn($inn);
 }

 /**
  * Строит NAME врача как транслит фамилии и ИНН.
  */
 private function buildDoctorName(string $lastName, string $inn): string
 {
  $lastNameSlug = $this->translit($this->normalizeText($lastName), 120);
  $normalizedInn = $this->normalizeInn($inn);

  if ($lastNameSlug !== '' && $normalizedInn !== '') {
   return $lastNameSlug . '-' . $normalizedInn;
  }

  if ($lastNameSlug !== '') {
   return $lastNameSlug;
  }

  if ($normalizedInn !== '') {
   return $normalizedInn;
  }

  return 'doctor';
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

 /**
  * Нормализует ИНН, оставляя только цифры.
  */
 private function normalizeInn(string $value): string
 {
  $value = trim($value);

  if ($value === '') {
   return '';
  }

  return preg_replace('/\D+/', '', $value) ?? '';
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
   $procedureId = (int)$procedureId;

   if ($procedureId > 0) {
    $result[] = $procedureId;
   }
  }

  return array_values(array_unique($result));
 }

 /**
  * Строит суффикс ORM-метода по коду поля/свойства.
  *
  * PROC_IDS_MULTI -> ProcIdsMulti
  */
 private function buildFieldMethodSuffix(string $fieldCode): string
 {
  return str_replace(
   ' ',
   '',
   ucwords(strtolower(str_replace('_', ' ', $fieldCode)))
  );
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
    return trim((string)$data[$key]);
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
    return trim((string)$data[$key]);
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
