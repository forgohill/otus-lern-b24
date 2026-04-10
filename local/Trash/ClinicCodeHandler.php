<?php

namespace App\Clinic\Event;

use App\Debug\Log;
use Bitrix\Iblock\IblockTable;

class ClinicCodeHandler
{
 private const DOCTOR_IBLOCK_CODE = 'doctors';
 private const PROCEDURE_IBLOCK_CODE = 'procedures';

 private const LAST_NAME_CODE = 'LAST_NAME';
 private const INDIVIDUAL_TAX_NUMBER = 'INDIVIDUAL_TAX_NUMBER';

 private const LOG_FILE = 'clinic_code';

 private static array $iblockIdCache = [];

 /**
  * Обрабатывает событие перед добавлением элемента инфоблока.
  *
  * Метод вызывается Bitrix до сохранения нового элемента.
  * Делегирует всю основную обработку в общий метод process().
  *
  * @param array $arFields Поля элемента инфоблока, переданные по ссылке.
  * @return bool true — разрешить сохранение, false — запретить сохранение.
  */
 public static function onBeforeElementAdd(array &$arFields): bool
 {
  return self::process($arFields, 'add');
 }

 /**
  * Обрабатывает событие перед обновлением элемента инфоблока.
  *
  * Метод вызывается Bitrix до изменения существующего элемента.
  * Делегирует всю основную обработку в общий метод process().
  *
  * @param array $arFields Поля элемента инфоблока, переданные по ссылке.
  * @return bool true — разрешить сохранение, false — запретить сохранение.
  */
 public static function onBeforeElementUpdate(array &$arFields): bool
 {
  return self::process($arFields, 'update');
 }

 /**
  * Выполняет общую маршрутизацию обработки элемента инфоблока.
  *
  * Подключает модуль iblock, определяет тип инфоблока,
  * пишет стартовый лог и передаёт управление в специализированную ветку:
  * для врачей или для процедур.
  *
  * @param array $arFields Поля элемента инфоблока, переданные по ссылке.
  * @param string $action Тип действия: add или update.
  * @return bool true — разрешить сохранение, false — запретить сохранение.
  */
 private static function process(array &$arFields, string $action): bool
 {
  if (!\CModule::IncludeModule('iblock')) {
   return true;
  }

  $iblockId = (int)($arFields['IBLOCK_ID'] ?? 0);
  $elementId = (int)($arFields['ID'] ?? 0);

  $doctorIblockId = self::getDoctorIblockId();
  $procedureIblockId = self::getProcedureIblockId();

  if (!in_array($iblockId, [$doctorIblockId, $procedureIblockId], true)) {
   return true;
  }

  try {
   Log::addLog([
    'step' => 'start',
    'action' => $action,
    'iblock_id' => $iblockId,
    'doctor_iblock_id' => $doctorIblockId,
    'procedure_iblock_id' => $procedureIblockId,
    'element_id' => $elementId,
    'incoming_name' => $arFields['NAME'] ?? null,
    'incoming_code' => $arFields['CODE'] ?? null,
   ], false, self::LOG_FILE, true);

   if ($iblockId === $doctorIblockId) {
    return self::processDoctor($arFields, $action, $iblockId, $elementId);
   }

   if ($iblockId === $procedureIblockId) {
    return self::processProcedure($arFields, $action, $iblockId, $elementId);
   }

   return true;
  } catch (\Throwable $e) {
   Log::addLog([
    'step' => 'exception',
    'action' => $action,
    'iblock_id' => $iblockId,
    'element_id' => $elementId,
    'message' => $e->getMessage(),
    'file' => $e->getFile(),
    'line' => $e->getLine(),
    'trace' => $e->getTraceAsString(),
   ], false, self::LOG_FILE, true);

   return true;
  }
 }

 /**
  * Обрабатывает бизнес-логику сохранения врача.
  *
  * Читает фамилию и ИНН, валидирует обязательность ИНН и его уникальность,
  * затем генерирует стандартные поля NAME и CODE.
  *
  * @param array $arFields Поля элемента инфоблока, переданные по ссылке.
  * @param string $action Тип действия: add или update.
  * @param int $iblockId ID инфоблока врачей.
  * @param int $elementId ID текущего элемента.
  * @return bool true — разрешить сохранение, false — запретить сохранение.
  */
 private static function processDoctor(array &$arFields, string $action, int $iblockId, int $elementId): bool
 {
  $lastName = self::readPropertyValue($arFields, $iblockId, self::LAST_NAME_CODE);
  $innRaw = self::readPropertyValue($arFields, $iblockId, self::INDIVIDUAL_TAX_NUMBER);
  $inn = self::normalizeInn($innRaw);

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
   return self::failValidation(
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

  $duplicate = self::findDoctorDuplicateByInn($iblockId, $inn, $elementId);

  if ($duplicate !== null) {
   return self::failValidation(
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

  $doctorCode = self::buildDoctorCode($inn);
  $doctorName = self::buildDoctorName($lastName, $inn);

  $arFields['CODE'] = $doctorCode;
  $arFields['NAME'] = $doctorName;

  Log::addLog([
   'step' => 'doctor_fields_generated',
   'action' => $action,
   'iblock_id' => $iblockId,
   'element_id' => $elementId,
   'generated_code' => $doctorCode,
   'generated_name' => $doctorName,
  ], false, self::LOG_FILE, true);

  return true;
 }

 /**
  * Обрабатывает бизнес-логику сохранения процедуры.
  *
  * Получает имя процедуры, строит из него символьный код и подменяет поле CODE.
  *
  * @param array $arFields Поля элемента инфоблока, переданные по ссылке.
  * @param string $action Тип действия: add или update.
  * @param int $iblockId ID инфоблока процедур.
  * @param int $elementId ID текущего элемента.
  * @return bool true — разрешить сохранение, false — запретить сохранение.
  */
 private static function processProcedure(array &$arFields, string $action, int $iblockId, int $elementId): bool
 {
  $name = self::readName($arFields, $elementId);
  $procedureCode = self::buildProcedureCode($name);

  $arFields['CODE'] = $procedureCode;

  Log::addLog([
   'step' => 'procedure_code_generated',
   'action' => $action,
   'iblock_id' => $iblockId,
   'element_id' => $elementId,
   'name' => $name,
   'generated_code' => $procedureCode,
  ], false, self::LOG_FILE, true);

  return true;
 }

 /**
  * Завершает обработку ошибкой валидации.
  *
  * Пишет ошибку в лог, передаёт сообщение в стандартный механизм Bitrix
  * через $APPLICATION->ThrowException() и возвращает false для запрета сохранения.
  *
  * @param string $message Текст ошибки валидации.
  * @param array $logContext Дополнительный контекст для записи в лог.
  * @return bool Всегда false.
  */
 private static function failValidation(string $message, array $logContext = []): bool
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
  * Ищет другого врача с таким же ИНН.
  *
  * При обновлении текущий элемент исключается из проверки.
  * Если найден дубль, возвращает краткую информацию о нём.
  *
  * @param int $iblockId ID инфоблока врачей.
  * @param string $inn Нормализованный ИНН для поиска.
  * @param int $currentElementId ID текущего элемента, который нужно исключить из проверки.
  * @return array|null Массив с данными дубля или null, если дубль не найден.
  */
 private static function findDoctorDuplicateByInn(int $iblockId, string $inn, int $currentElementId = 0): ?array
 {
  $res = \CIBlockElement::GetList(
   ['ID' => 'ASC'],
   ['IBLOCK_ID' => $iblockId],
   false,
   false,
   ['ID', 'IBLOCK_ID', 'NAME']
  );

  while ($row = $res->Fetch()) {
   $candidateId = (int)$row['ID'];

   if ($currentElementId > 0 && $candidateId === $currentElementId) {
    continue;
   }

   $candidateInn = self::normalizeInn(
    self::readCurrentPropertyValue($iblockId, $candidateId, self::INDIVIDUAL_TAX_NUMBER)
   );

   if ($candidateInn === '') {
    continue;
   }

   if ($candidateInn === $inn) {
    return [
     'ID' => $candidateId,
     'NAME' => (string)$row['NAME'],
     'INN' => $candidateInn,
    ];
   }
  }

  return null;
 }

 /**
  * Возвращает ID инфоблока врачей.
  *
  * @return int ID инфоблока.
  */
 private static function getDoctorIblockId(): int
 {
  return self::getIblockIdByCode(self::DOCTOR_IBLOCK_CODE);
 }

 /**
  * Возвращает ID инфоблока процедур.
  *
  * @return int ID инфоблока.
  */
 private static function getProcedureIblockId(): int
 {
  return self::getIblockIdByCode(self::PROCEDURE_IBLOCK_CODE);
 }

 /**
  * Находит ID инфоблока по его символьному коду.
  *
  * Использует статический кэш внутри одного PHP-запроса, чтобы не ходить в базу повторно.
  *
  * @param string $iblockCode Символьный код инфоблока.
  * @return int ID инфоблока или 0, если инфоблок не найден.
  */
 private static function getIblockIdByCode(string $iblockCode): int
 {
  if (isset(self::$iblockIdCache[$iblockCode])) {
   return self::$iblockIdCache[$iblockCode];
  }

  $row = IblockTable::getList([
   'select' => ['ID', 'CODE'],
   'filter' => ['=CODE' => $iblockCode],
   'limit' => 1,
  ])->fetch();

  $id = (int)($row['ID'] ?? 0);
  self::$iblockIdCache[$iblockCode] = $id;

  Log::addLog([
   'step' => 'resolve_iblock_id_by_code',
   'iblock_code' => $iblockCode,
   'resolved_id' => $id,
  ], false, self::LOG_FILE, true);

  return $id;
 }

 /**
  * Строит символьный код врача.
  *
  * В текущей бизнес-логике CODE врача равен нормализованному ИНН.
  *
  * @param string $inn ИНН врача.
  * @return string Нормализованный ИНН.
  */
 private static function buildDoctorCode(string $inn): string
 {
  return self::normalizeInn($inn);
 }

 /**
  * Строит поле NAME для врача.
  *
  * Формат: транслит фамилии + "-" + ИНН.
  *
  * @param string $lastName Фамилия врача.
  * @param string $inn ИНН врача.
  * @return string Сформированное имя элемента.
  */
 private static function buildDoctorName(string $lastName, string $inn): string
 {
  $lastNameSlug = self::translit(self::normalizeText($lastName), 120);
  $normalizedInn = self::normalizeInn($inn);

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
  * Строит символьный код процедуры по её названию.
  *
  * @param string $name Название процедуры.
  * @return string Транслитерированный код процедуры.
  */
 private static function buildProcedureCode(string $name): string
 {
  return self::translit(self::normalizeText($name), 150);
 }

 /**
  * Транслитерирует строку в символьный код Bitrix-формата.
  *
  * @param string $value Исходное значение.
  * @param int $maxLen Максимальная длина результата.
  * @return string Транслитерированная строка.
  */
 private static function translit(string $value, int $maxLen = 100): string
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
  * Нормализует текстовое значение.
  *
  * Убирает пробелы по краям и схлопывает подряд идущие пробелы.
  *
  * @param string $value Исходная строка.
  * @return string Нормализованная строка.
  */
 private static function normalizeText(string $value): string
 {
  $value = trim($value);
  $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

  return $value;
 }

 /**
  * Нормализует ИНН.
  *
  * Удаляет все символы, кроме цифр.
  *
  * @param string $value Исходное значение ИНН.
  * @return string Нормализованный ИНН.
  */
 private static function normalizeInn(string $value): string
 {
  $value = trim($value);

  if ($value === '') {
   return '';
  }

  return preg_replace('/\D+/', '', $value) ?? '';
 }

 /**
  * Получает имя элемента из входящих полей или из базы.
  *
  * @param array $arFields Поля элемента.
  * @param int $elementId ID элемента.
  * @return string Имя элемента.
  */
 private static function readName(array $arFields, int $elementId): string
 {
  $name = trim((string)($arFields['NAME'] ?? ''));

  if ($name !== '') {
   return $name;
  }

  if ($elementId <= 0) {
   return '';
  }

  $res = \CIBlockElement::GetList(
   [],
   ['ID' => $elementId],
   false,
   false,
   ['ID', 'NAME']
  );

  if ($row = $res->Fetch()) {
   return trim((string)$row['NAME']);
  }

  return '';
 }

 /**
  * Получает значение свойства из входящих данных или из базы.
  *
  * Метод умеет читать свойство по символьному коду, по числовому ID свойства
  * и при необходимости дочитывает текущее значение существующего элемента.
  *
  * @param array $arFields Поля элемента.
  * @param int $iblockId ID инфоблока.
  * @param string $propertyCode Символьный код свойства.
  * @return string Строковое значение свойства.
  */
 private static function readPropertyValue(array $arFields, int $iblockId, string $propertyCode): string
 {
  $propertyValues = $arFields['PROPERTY_VALUES'] ?? [];

  if (array_key_exists($propertyCode, $propertyValues)) {
   return self::extractScalarValue($propertyValues[$propertyCode]);
  }

  $propertyId = self::getPropertyIdByCode($iblockId, $propertyCode);

  if ($propertyId > 0 && array_key_exists($propertyId, $propertyValues)) {
   return self::extractScalarValue($propertyValues[$propertyId]);
  }

  $elementId = (int)($arFields['ID'] ?? 0);

  if ($elementId > 0) {
   return self::readCurrentPropertyValue($iblockId, $elementId, $propertyCode);
  }

  return '';
 }

 /**
  * Получает числовой ID свойства по его символьному коду.
  *
  * @param int $iblockId ID инфоблока.
  * @param string $propertyCode Символьный код свойства.
  * @return int ID свойства или 0, если свойство не найдено.
  */
 private static function getPropertyIdByCode(int $iblockId, string $propertyCode): int
 {
  $res = \CIBlockProperty::GetList(
   [],
   [
    'IBLOCK_ID' => $iblockId,
    'CODE' => $propertyCode,
   ]
  );

  if ($row = $res->Fetch()) {
   return (int)$row['ID'];
  }

  return 0;
 }

 /**
  * Дочитывает текущее значение свойства существующего элемента из базы.
  *
  * @param int $iblockId ID инфоблока.
  * @param int $elementId ID элемента.
  * @param string $propertyCode Символьный код свойства.
  * @return string Значение свойства.
  */
 private static function readCurrentPropertyValue(int $iblockId, int $elementId, string $propertyCode): string
 {
  $res = \CIBlockElement::GetProperty(
   $iblockId,
   $elementId,
   ['sort' => 'asc'],
   ['CODE' => $propertyCode]
  );

  if ($row = $res->Fetch()) {
   return trim((string)($row['VALUE'] ?? ''));
  }

  return '';
 }

 /**
  * Извлекает строковое значение из скаляра или из вложенного массива Bitrix-формата.
  *
  * Используется для чтения значений свойств из разных форматов PROPERTY_VALUES.
  *
  * @param mixed $value Исходное значение.
  * @return string Извлечённое строковое значение.
  */
 private static function extractScalarValue($value): string
 {
  if (is_scalar($value)) {
   return trim((string)$value);
  }

  if (!is_array($value)) {
   return '';
  }

  if (array_key_exists('VALUE', $value) && !is_array($value['VALUE'])) {
   return trim((string)$value['VALUE']);
  }

  foreach ($value as $item) {
   $result = self::extractScalarValue($item);

   if ($result !== '') {
    return $result;
   }
  }

  return '';
 }
}
