<?php

namespace App\Clinic\Event;

use App\Clinic\Processor\DoctorElementProcessor;
use App\Clinic\Processor\ProcedureElementProcessor;
use App\Clinic\Service\IblockResolver;
use App\Debug\Log;
use Bitrix\Main\Loader;


class ClinicCodeHandler
{
 private const DOCTOR_IBLOCK_CODE = 'doctors';
 private const PROCEDURE_IBLOCK_CODE = 'procedures';
 private const LOG_FILE = 'clinic_code';

 /**
  * Обрабатывает событие перед добавлением элемента инфоблока.
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
  * @param array $arFields Поля элемента инфоблока, переданные по ссылке.
  * @return bool true — разрешить сохранение, false — запретить сохранение.
  */
 public static function onBeforeElementUpdate(array &$arFields): bool
 {
  return self::process($arFields, 'update');
 }

 /**
  * Выполняет маршрутизацию по типу инфоблока и передаёт обработку профильному процессору.
  *
  * @param array $arFields Поля элемента инфоблока, переданные по ссылке.
  * @param string $action Тип действия: add или update.
  * @return bool true — разрешить сохранение, false — запретить сохранение.
  */
 private static function process(array &$arFields, string $action): bool
 {
  // if (!\CModule::IncludeModule('iblock')) {
  //  return true;
  // }
  Loader::includeModule('iblock');
  $iblockId = (int)($arFields['IBLOCK_ID'] ?? 0);
  $elementId = (int)($arFields['ID'] ?? 0);

  try {
   $resolver = new IblockResolver();

   $doctorIblockId = $resolver->getIdByCode(self::DOCTOR_IBLOCK_CODE);
   $procedureIblockId = $resolver->getIdByCode(self::PROCEDURE_IBLOCK_CODE);

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
    return (new DoctorElementProcessor())->process($arFields, $action, $iblockId, $elementId);
   }

   if ($iblockId === $procedureIblockId) {
    return (new ProcedureElementProcessor())->process($arFields, $action, $iblockId, $elementId);
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
}
