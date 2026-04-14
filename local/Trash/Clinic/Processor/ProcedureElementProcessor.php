<?php

namespace App\Clinic\Processor;

use App\Debug\Log;
use App\Iblock\Processor\ElementProcessorInterface;

/**
 * Processor элементов инфоблока процедур.
 *
 * Класс содержит только procedure-специфичную логику:
 * - чтение имени элемента;
 * - генерацию CODE из названия процедуры;
 * - логирование результата.
 */
class ProcedureElementProcessor implements ElementProcessorInterface
{
 private const LOG_FILE = 'clinic_code';

 /**
  * Выполняет обработку элемента процедуры перед сохранением.
  *
  * @param array $arFields Поля элемента инфоблока, переданные по ссылке.
  * @param string $action Тип действия: add или update.
  * @param int $iblockId ID инфоблока процедур.
  * @param int $elementId ID текущего элемента.
  * @return bool true — разрешить сохранение, false — запретить сохранение.
  */
 public function process(array &$arFields, string $action, int $iblockId, int $elementId): bool
 {
  $name = $this->readName($arFields, $elementId);
  $procedureCode = $this->buildProcedureCode($name);

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
  * Строит CODE процедуры по её названию.
  *
  * @param string $name Название процедуры.
  * @return string Транслитерированный символьный код.
  */
 private function buildProcedureCode(string $name): string
 {
  return $this->translit($this->normalizeText($name), 150);
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
  * Получает имя элемента из arFields или из базы.
  *
  * @param array $arFields Поля элемента.
  * @param int $elementId ID элемента.
  * @return string Имя элемента.
  */
 private function readName(array $arFields, int $elementId): string
 {
  $name = trim((string) ($arFields['NAME'] ?? ''));

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
   return trim((string) $row['NAME']);
  }

  return '';
 }
}
