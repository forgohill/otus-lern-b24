<?php

declare(strict_types=1);

namespace App\Clinic\Service;

use Bitrix\Iblock\Iblock;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\SystemException;

/**
 * Базовый сервис записи в инфоблок через ORM.
 */
abstract class AbstractIblockFormService
{
 /**
  * Кеш найденного ID инфоблока.
  */
 private ?int $resolvedIblockId = null;

 /**
  * Дочерний класс обязан вернуть CODE инфоблока.
  */
 abstract protected function getIblockCode(): string;

 /**
  * По умолчанию возвращает ORM data class через wakeUp().
  *
  * DoctorFormService переопределяет этот метод и использует
  * явную ORM-модель ElementDoctorsTable::class.
  *
  * @throws SystemException
  */
 protected function getDataClass(): string
 {
  $this->ensureIblockModuleLoaded();

  return Iblock::wakeUp($this->getIblockId())->getEntityDataClass();
 }

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

  $this->ensureIblockModuleLoaded();

  $iblockRow = IblockTable::getList([
   'select' => ['ID'],
   'filter' => ['=CODE' => $this->getIblockCode()],
   'limit' => 1,
  ])->fetch();

  if (!$iblockRow || empty($iblockRow['ID'])) {
   throw new SystemException(
    sprintf('Инфоблок с CODE "%s" не найден.', $this->getIblockCode())
   );
  }

  $this->resolvedIblockId = (int)$iblockRow['ID'];

  return $this->resolvedIblockId;
 }

 /**
  * Создаёт новый ORM-объект элемента.
  *
  * @throws SystemException
  */
 protected function createElementObject(): EntityObject
 {
  $this->ensureIblockModuleLoaded();

  $dataClass = $this->getDataClass();

  return $dataClass::createObject();
 }

 /**
  * Загружает ORM-объект элемента по ID.
  *
  * @param string[] $select
  * @throws SystemException
  */
 protected function getElementObjectById(int $id, array $select = ['*']): ?EntityObject
 {
  if ($id <= 0) {
   return null;
  }

  $this->ensureIblockModuleLoaded();

  $dataClass = $this->getDataClass();

  return $dataClass::getByPrimary($id, [
   'select' => $select,
  ])->fetchObject();
 }

 /**
  * Преобразует ошибки ORM-результата в простой массив строк.
  *
  * @return string[]
  */
 protected function extractOrmErrors(Result $result, string $defaultMessage): array
 {
  $errors = $result->getErrorMessages();

  if ($errors === []) {
   return [$defaultMessage];
  }

  return array_values(array_filter(
   array_map('trim', $errors),
   static fn(string $error): bool => $error !== ''
  ));
 }

 /**
  * Гарантирует подключение модуля iblock.
  *
  * @throws SystemException
  */
 protected function ensureIblockModuleLoaded(): void
 {
  if (!Loader::includeModule('iblock')) {
   throw new SystemException('Модуль iblock не подключен.');
  }
 }
}
