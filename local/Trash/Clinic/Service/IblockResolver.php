<?php

declare(strict_types=1);

namespace App\Clinic\Service;

use App\Clinic\Config\ClinicIblockCodes;
use App\Clinic\Processor\DoctorElementProcessor;
use App\Clinic\Processor\ProcedureElementProcessor;
use App\Debug\Log;
use App\Iblock\Processor\ElementProcessorInterface;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

/**
 * Resolver clinic-инфоблоков.
 *
 * Отвечает за:
 * - получение ID инфоблока по его символьному коду;
 * - определение processor-класса по ID инфоблока.
 */
class IblockResolver
{
 private const LOG_FILE = 'iblock_event';

 /**
  * Карта:
  * CODE инфоблока -> processor-класс.
  *
  * @var array<string, class-string<ElementProcessorInterface>>
  */
 private const PROCESSOR_MAP = [
  ClinicIblockCodes::DOCTORS => DoctorElementProcessor::class,
  ClinicIblockCodes::PROCEDURES => ProcedureElementProcessor::class,
 ];

 /**
  * Кэш найденных ID инфоблоков.
  *
  * @var array<string, int>
  */
 private static array $iblockIdCache = [];

 /**
  * Возвращает ID инфоблока по его символьному коду.
  *
  * @throws SystemException
  */
 public function getIdByCode(string $iblockCode): int
 {
  $iblockCode = trim($iblockCode);

  if ($iblockCode === '') {
   throw new SystemException('Не передан символьный код инфоблока.');
  }

  if (isset(self::$iblockIdCache[$iblockCode])) {
   return self::$iblockIdCache[$iblockCode];
  }

  if (!Loader::includeModule('iblock')) {
   throw new SystemException('Модуль iblock не подключен.');
  }

  $row = IblockTable::getList([
   'select' => ['ID', 'CODE'],
   'filter' => ['=CODE' => $iblockCode],
   'limit' => 1,
  ])->fetch();

  if (!$row || empty($row['ID'])) {
   Log::addLog([
    'step' => 'resolve_iblock_id_by_code_error',
    'iblock_code' => $iblockCode,
    'resolved_id' => 0,
    'message' => 'Инфоблок не найден',
   ], false, self::LOG_FILE, true);

   throw new SystemException(
    sprintf('Инфоблок с CODE "%s" не найден.', $iblockCode)
   );
  }

  $id = (int) $row['ID'];
  self::$iblockIdCache[$iblockCode] = $id;

  Log::addLog([
   'step' => 'resolve_iblock_id_by_code',
   'iblock_code' => $iblockCode,
   'resolved_id' => $id,
  ], false, self::LOG_FILE, true);

  return $id;
 }

 /**
  * Определяет processor-класс по ID инфоблока.
  *
  * @return string|null
  */
 public function resolveProcessorClassByIblockId(int $iblockId): ?string
 {
  if ($iblockId <= 0) {
   Log::addLog([
    'step' => 'resolve_processor_class_skipped',
    'reason' => 'empty_iblock_id',
    'iblock_id' => $iblockId,
   ], false, self::LOG_FILE, true);

   return null;
  }

  foreach (self::PROCESSOR_MAP as $iblockCode => $processorClass) {
   if ($this->getIdByCode($iblockCode) === $iblockId) {
    Log::addLog([
     'step' => 'resolve_processor_class_success',
     'iblock_id' => $iblockId,
     'iblock_code' => $iblockCode,
     'processor_class' => $processorClass,
    ], false, self::LOG_FILE, true);

    return $processorClass;
   }
  }

  Log::addLog([
   'step' => 'resolve_processor_class_miss',
   'iblock_id' => $iblockId,
  ], false, self::LOG_FILE, true);

  return null;
 }

 /**
  * Возвращает processor-объект по ID инфоблока.
  */
 public function resolveProcessorByIblockId(int $iblockId): ?ElementProcessorInterface
 {
  $processorClass = $this->resolveProcessorClassByIblockId($iblockId);

  if ($processorClass === null) {
   return null;
  }

  $processor = new $processorClass();

  if (!$processor instanceof ElementProcessorInterface) {
   throw new \RuntimeException(sprintf(
    'Processor "%s" must implement %s.',
    $processorClass,
    ElementProcessorInterface::class
   ));
  }

  return $processor;
 }

 /**
  * Возвращает карту поддерживаемых clinic-инфоблоков.
  *
  * @return array<string, class-string<ElementProcessorInterface>>
  */
 public function getProcessorMap(): array
 {
  return self::PROCESSOR_MAP;
 }
}
