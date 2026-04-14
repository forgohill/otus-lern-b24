<?php

namespace App\Iblock\Processor;

/**
 * Контракт для processor-классов элементов инфоблоков.
 *
 * Любой processor, который умеет обрабатывать элемент инфоблока
 * перед сохранением, должен реализовать этот интерфейс.
 */
interface ElementProcessorInterface
{
 /**
  * Выполняет обработку элемента инфоблока перед сохранением.
  *
  * @param array $arFields Поля элемента инфоблока, переданные по ссылке.
  * @param string $action Тип действия: add или update.
  * @param int $iblockId ID инфоблока.
  * @param int $elementId ID текущего элемента.
  * @return bool true — разрешить сохранение, false — запретить сохранение.
  */
 public function process(array &$arFields, string $action, int $iblockId, int $elementId): bool;
}
