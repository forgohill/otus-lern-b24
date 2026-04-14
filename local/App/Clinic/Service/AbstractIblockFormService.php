<?php

declare(strict_types=1);

namespace App\Clinic\Service;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

/**
 * Базовый сервис записи в инфоблок.
 */
abstract class AbstractIblockFormService
{
 /**
  * Кешируем найденный ID инфоблока,
  * чтобы не искать его по CODE каждый раз.
  */
 private ?int $resolvedIblockId = null;

 /**
  * Дочерний класс обязан вернуть CODE инфоблока.
  *
  * Пример:
  * return 'doctors';
  */
 abstract protected function getIblockCode(): string;

 /**
  * Возвращает ID инфоблока по его CODE.
  *
  * @throws SystemException
  */
 protected function getIblockId(): int
 {
  if ($this->resolvedIblockId !== null) {
   return $this->resolvedIblockId;
  }

  if (!Loader::includeModule('iblock')) {
   throw new SystemException('Модуль iblock не подключен.');
  }

  $iblockResult = \CIBlock::GetList(
   [],
   [
    'CODE' => $this->getIblockCode(),
    'CHECK_PERMISSIONS' => 'N',
   ]
  );

  $iblockRow = $iblockResult->Fetch();

  if (!$iblockRow || empty($iblockRow['ID'])) {
   throw new SystemException(
    sprintf('Инфоблок с CODE "%s" не найден.', $this->getIblockCode())
   );
  }

  $this->resolvedIblockId = (int)$iblockRow['ID'];

  return $this->resolvedIblockId;
 }

 /**
  * Возвращает объект старого API для Add/Update.
  */
 protected function getElementApi(): \CIBlockElement
 {
  return new \CIBlockElement();
 }
}
