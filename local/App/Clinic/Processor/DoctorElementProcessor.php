<?php

namespace App\Clinic\Processor;

use App\Debug\Log;
use App\Iblock\Processor\ElementProcessorInterface;

/**
 * Processor элементов инфоблока врачей.
 *
 * Класс содержит только doctor-специфичную бизнес-логику:
 * - чтение фамилии и ИНН;
 * - нормализацию данных;
 * - валидацию обязательности, формата и уникальности ИНН;
 * - генерацию NAME и CODE;
 * - логирование doctor-процесса.
 */
class DoctorElementProcessor implements ElementProcessorInterface
{
 private const LAST_NAME_CODE = 'LAST_NAME';
 private const INDIVIDUAL_TAX_NUMBER = 'INDIVIDUAL_TAX_NUMBER';
 private const LOG_FILE = 'clinic_code';

 /**
  * Выполняет обработку элемента врача перед сохранением.
  *
  * @param array $arFields Поля элемента инфоблока, переданные по ссылке.
  * @param string $action Тип действия: add или update.
  * @param int $iblockId ID инфоблока врачей.
  * @param int $elementId ID текущего элемента.
  * @return bool true — разрешить сохранение, false — запретить сохранение.
  */
 public function process(array &$arFields, string $action, int $iblockId, int $elementId): bool
 {
  $lastName = $this->readPropertyValue($arFields, $iblockId, self::LAST_NAME_CODE);
  $innRaw = $this->readPropertyValue($arFields, $iblockId, self::INDIVIDUAL_TAX_NUMBER);
  $inn = $this->normalizeInn($innRaw);

  Log::addLog([
   'step' => 'doctor_input_collected',
   'action' => $action,
   'iblock_id' => $iblockId,
   'element_id' => $elementId,
   'last_name_raw' => $lastName,
   'inn_raw' => $innRaw,
   'inn_normalized' => $inn,
  ], false, self::LOG_FILE, true);

  if ($inn === '') {
   return $this->failValidation(
    'Не заполнено обязательное свойство ИНН.',
    [
     'step' => 'doctor_validation_failed',
     'reason' => 'empty_inn',
     'action' => $action,
     'iblock_id' => $iblockId,
     'element_id' => $elementId,
    ]
   );
  }

  if (!$this->isValidInn($inn)) {
   return $this->failValidation(
    'ИНН должен состоять ровно из 12 цифр и не может начинаться с 0.',
    [
     'step' => 'doctor_validation_failed',
     'reason' => 'invalid_inn_format',
     'action' => $action,
     'iblock_id' => $iblockId,
     'element_id' => $elementId,
     'inn_raw' => $innRaw,
     'inn_normalized' => $inn,
    ]
   );
  }

  $duplicate = $this->findDuplicateByInn($iblockId, $inn, $elementId);

  if ($duplicate !== null) {
   return $this->failValidation(
    'Элемент врача с таким ИНН уже существует. ID дубля: ' . $duplicate['ID'],
    [
     'step' => 'doctor_validation_failed',
     'reason' => 'duplicate_inn',
     'action' => $action,
     'iblock_id' => $iblockId,
     'element_id' => $elementId,
     'duplicate_id' => $duplicate['ID'],
     'duplicate_name' => $duplicate['NAME'],
     'duplicate_inn' => $duplicate['INN'],
    ]
   );
  }

  $arFields['CODE'] = $this->buildDoctorCode($inn);
  $arFields['NAME'] = $this->buildDoctorName($lastName, $inn);

  Log::addLog([
   'step' => 'doctor_fields_generated',
   'action' => $action,
   'iblock_id' => $iblockId,
   'element_id' => $elementId,
   'generated_code' => $arFields['CODE'],
   'generated_name' => $arFields['NAME'],
  ], false, self::LOG_FILE, true);

  return true;
 }

 /**
  * Завершает обработку ошибкой валидации.
  *
  * @param string $message Текст ошибки.
  * @param array $logContext Дополнительный контекст для лога.
  * @return bool Всегда false.
  */
 private function failValidation(string $message, array $logContext = []): bool
 {
  global $APPLICATION;

  Log::addLog(array_merge([
   'step' => 'validation_error',
   'message' => $message,
  ], $logContext), false, self::LOG_FILE, true);

  if (is_object($APPLICATION)) {
   $APPLICATION->ThrowException($message);
  }

  return false;
 }

 /**
  * Проверяет формат ИНН врача.
  *
  * Правило:
  * - только 12 цифр;
  * - первая цифра не может быть 0.
  *
  * @param string $inn Нормализованный ИНН.
  * @return bool true, если формат корректный.
  */
 private function isValidInn(string $inn): bool
 {
  return preg_match('/^[1-9][0-9]{11}$/', $inn) === 1;
 }

 /**
  * Ищет дубликат врача по ИНН.
  *
  * @param int $iblockId ID инфоблока врачей.
  * @param string $inn Нормализованный ИНН.
  * @param int $currentElementId ID текущего элемента, который нужно исключить.
  * @return array|null Массив дубля или null.
  */
 private function findDuplicateByInn(int $iblockId, string $inn, int $currentElementId = 0): ?array
 {
  $res = \CIBlockElement::GetList(
   ['ID' => 'ASC'],
   ['IBLOCK_ID' => $iblockId],
   false,
   false,
   ['ID', 'IBLOCK_ID', 'NAME']
  );

  while ($row = $res->Fetch()) {
   $candidateId = (int) $row['ID'];

   if ($currentElementId > 0 && $candidateId === $currentElementId) {
    continue;
   }

   $candidateInn = $this->normalizeInn(
    $this->readCurrentPropertyValue($iblockId, $candidateId, self::INDIVIDUAL_TAX_NUMBER)
   );

   if ($candidateInn === '') {
    continue;
   }

   if ($candidateInn === $inn) {
    return [
     'ID' => $candidateId,
     'NAME' => (string) $row['NAME'],
     'INN' => $candidateInn,
    ];
   }
  }

  return null;
 }

 /**
  * Строит CODE врача из ИНН.
  *
  * @param string $inn ИНН врача.
  * @return string Нормализованный ИНН.
  */
 private function buildDoctorCode(string $inn): string
 {
  return $this->normalizeInn($inn);
 }

 /**
  * Строит NAME врача как транслит фамилии и ИНН.
  *
  * @param string $lastName Фамилия врача.
  * @param string $inn ИНН врача.
  * @return string Сформированное имя элемента.
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
  *
  * @param string $value Исходная строка.
  * @param int $maxLen Максимальная длина результата.
  * @return string Транслитерированная строка.
  */
 private function translit(string $value, int $maxLen = 100): string
 {
  return (string) \CUtil::translit($value, 'ru', [
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
  *
  * @param string $value Исходное значение.
  * @return string Нормализованная строка.
  */
 private function normalizeText(string $value): string
 {
  $value = trim($value);
  $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

  return $value;
 }

 /**
  * Нормализует ИНН, оставляя только цифры.
  *
  * @param string $value Исходное значение.
  * @return string Нормализованный ИНН.
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
  * Читает значение свойства из arFields или из базы.
  *
  * @param array $arFields Поля элемента.
  * @param int $iblockId ID инфоблока.
  * @param string $propertyCode Код свойства.
  * @return string Строковое значение свойства.
  */
 private function readPropertyValue(array $arFields, int $iblockId, string $propertyCode): string
 {
  $propertyValues = $arFields['PROPERTY_VALUES'] ?? [];

  if (array_key_exists($propertyCode, $propertyValues)) {
   return $this->extractScalarValue($propertyValues[$propertyCode]);
  }

  $propertyId = $this->getPropertyIdByCode($iblockId, $propertyCode);

  if ($propertyId > 0 && array_key_exists($propertyId, $propertyValues)) {
   return $this->extractScalarValue($propertyValues[$propertyId]);
  }

  $elementId = (int) ($arFields['ID'] ?? 0);

  if ($elementId > 0) {
   return $this->readCurrentPropertyValue($iblockId, $elementId, $propertyCode);
  }

  return '';
 }

 /**
  * Находит ID свойства по его символьному коду.
  *
  * @param int $iblockId ID инфоблока.
  * @param string $propertyCode Код свойства.
  * @return int ID свойства или 0.
  */
 private function getPropertyIdByCode(int $iblockId, string $propertyCode): int
 {
  $res = \CIBlockProperty::GetList(
   [],
   [
    'IBLOCK_ID' => $iblockId,
    'CODE' => $propertyCode,
   ]
  );

  if ($row = $res->Fetch()) {
   return (int) $row['ID'];
  }

  return 0;
 }

 /**
  * Читает текущее значение свойства элемента из базы.
  *
  * @param int $iblockId ID инфоблока.
  * @param int $elementId ID элемента.
  * @param string $propertyCode Код свойства.
  * @return string Значение свойства.
  */
 private function readCurrentPropertyValue(int $iblockId, int $elementId, string $propertyCode): string
 {
  $res = \CIBlockElement::GetProperty(
   $iblockId,
   $elementId,
   ['sort' => 'asc'],
   ['CODE' => $propertyCode]
  );

  if ($row = $res->Fetch()) {
   return trim((string) ($row['VALUE'] ?? ''));
  }

  return '';
 }

 /**
  * Извлекает скалярное значение из Bitrix-массива свойства.
  *
  * @param mixed $value Исходное значение.
  * @return string Строковое значение.
  */
 private function extractScalarValue($value): string
 {
  if (is_scalar($value)) {
   return trim((string) $value);
  }

  if (!is_array($value)) {
   return '';
  }

  if (array_key_exists('VALUE', $value) && !is_array($value['VALUE'])) {
   return trim((string) $value['VALUE']);
  }

  foreach ($value as $item) {
   $result = $this->extractScalarValue($item);

   if ($result !== '') {
    return $result;
   }
  }

  return '';
 }
}
