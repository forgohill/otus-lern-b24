<?php

namespace App\Iblock\Event;

use App\Clinic\Service\IblockResolver;
use App\Debug\Log;
use Bitrix\Main\Loader;

/**
 * Глобальная точка входа для событий элементов инфоблоков.
 *
 * Класс не содержит бизнес-логики конкретного домена.
 * Его задача:
 * - принять событие Bitrix;
 * - подключить модуль iblock;
 * - определить подходящий processor через resolver;
 * - делегировать обработку профильному processor-классу.
 */
class ElementEventHandler
{
 private const LOG_FILE = 'iblock_event';

 /**
  * Обрабатывает событие перед добавлением элемента инфоблока.
  *
  * @param array $arFields Поля элемента, переданные Bitrix по ссылке.
  * @return bool true — разрешить сохранение, false — запретить сохранение.
  */
 public static function onBeforeElementAdd(array &$arFields): bool
 {
  return self::handle($arFields, 'add');
 }

 /**
  * Обрабатывает событие перед обновлением элемента инфоблока.
  *
  * @param array $arFields Поля элемента, переданные Bitrix по ссылке.
  * @return bool true — разрешить сохранение, false — запретить сохранение.
  */
 public static function onBeforeElementUpdate(array &$arFields): bool
 {
  return self::handle($arFields, 'update');
 }

 /**
  * Выполняет общую обработку события и делегирует выполнение processor-классу.
  *
  * @param array $arFields Поля элемента инфоблока.
  * @param string $action Тип действия: add или update.
  * @return bool true — разрешить сохранение, false — запретить сохранение.
  */
 private static function handle(array &$arFields, string $action): bool
 {
  if (!Loader::includeModule('iblock')) {
   return true;
  }

  $iblockId = (int) ($arFields['IBLOCK_ID'] ?? 0);
  $elementId = (int) ($arFields['ID'] ?? 0);

  try {
   $resolver = new IblockResolver();
   $processor = $resolver->resolveProcessorByIblockId($iblockId);

   Log::addLog([
    'step' => 'event_handler_start',
    'action' => $action,
    'iblock_id' => $iblockId,
    'element_id' => $elementId,
    'processor_class' => $processor ? get_class($processor) : null,
    'incoming_name' => $arFields['NAME'] ?? null,
    'incoming_code' => $arFields['CODE'] ?? null,
   ], false, self::LOG_FILE, true);

   if ($processor === null) {
    return true;
   }

   return $processor->process($arFields, $action, $iblockId, $elementId);
  } catch (\Throwable $e) {
   Log::addLog([
    'step' => 'event_handler_exception',
    'action' => $action,
    'iblock_id' => $iblockId,
    'element_id' => $elementId,
    'message' => $e->getMessage(),
    'file' => $e->getFile(),
    'line' => $e->getLine(),
    'trace' => $e->getTraceAsString(),
   ], false, self::LOG_FILE, true);

   /**
    * Здесь оставляю текущее поведение мягким:
    * если произошла инфраструктурная ошибка handler-уровня,
    * мы не блокируем сохранение автоматически.
    *
    * Это соответствует текущей логике ClinicCodeHandler.
    */
   return true;
  }
 }
}
