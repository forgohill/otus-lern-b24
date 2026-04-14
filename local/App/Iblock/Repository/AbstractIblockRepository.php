<?php

declare(strict_types=1);

namespace App\Iblock\Repository;

use Bitrix\Iblock\Iblock;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\SystemException;

/**
 * Базовый read-only репозиторий для работы с элементами инфоблока через D7 ORM.
 */

abstract class AbstractIblockRepository
{
 /**
  * Кешируем найденный ID инфоблока,
  * чтобы не делать CIBlock::GetList() при каждом вызове.
  */
 private ?int $resolvedIblockId = null;

 /**
  * Конкретный репозиторий обязан вернуть символьный код инфоблока.
  *
  * Пример:
  * return 'doctors';
  */
 abstract public function getIblockCode(): string;

 /**
  * Возвращает ID инфоблока по его символьному коду.
  *
  * Логика:
  * 1) ищем инфоблок по CODE через старый API CIBlock::GetList();
  * 2) берём его ID;
  * 3) кешируем результат в свойстве объекта.
  *
  * @throws SystemException
  */
 public function getIblockId(): int
 {
  // Если уже нашли ID раньше — повторно в БД не идём.
  if ($this->resolvedIblockId !== null) {
   return $this->resolvedIblockId;
  }

  // Для работы с инфоблоками модуль iblock должен быть подключён.
  if (!Loader::includeModule('iblock')) {
   throw new SystemException('Модуль iblock не подключен.');
  }

  // Ищем инфоблок по символьному коду.
  $iblockResult = \CIBlock::GetList(
   [],
   [
    'CODE' => $this->getIblockCode(),
    'CHECK_PERMISSIONS' => 'N',
   ]
  );

  $iblockRow = $iblockResult->Fetch();

  // Если ничего не нашли — это уже архитектурная ошибка,
  // значит CODE неверный или инфоблок не существует.
  if (!$iblockRow || empty($iblockRow['ID'])) {
   throw new SystemException(
    sprintf(
     'Инфоблок с CODE "%s" не найден.',
     $this->getIblockCode()
    )
   );
  }

  $this->resolvedIblockId = (int) $iblockRow['ID'];

  return $this->resolvedIblockId;
 }

 /**
  * Возвращает ORM Data Class для конкретного инфоблока.
  *
  * Пример результата:
  * Bitrix\Iblock\Elements\ElementDoctorsTable
  *
  * @throws SystemException
  */
 public function getDataClass(): string
 {
  return Iblock::wakeUp($this->getIblockId())->getEntityDataClass();
 }

 /**
  * Возвращает ORM Query-объект.
  *
  * Нужен, если хочется собирать запрос вручную:
  * - select
  * - filter
  * - order
  * - runtime
  */
 public function query(): Query
 {
  $dataClass = $this->getDataClass();

  return $dataClass::query();
 }

 /**
  * Универсальная обёртка над getList().
  *
  * Пример:
  * [
  *     'select' => ['ID', 'NAME'],
  *     'filter' => ['ACTIVE' => 'Y'],
  *     'order'  => ['ID' => 'DESC'],
  * ]
  */
 public function getList(array $parameters = []): Result
 {
  $dataClass = $this->getDataClass();

  return $dataClass::getList($parameters);
 }

 /**
  * Получает один элемент инфоблока по ID элемента.
  *
  * Возвращает ORM-объект или null, если запись не найдена.
  */
 public function getById(int $id): ?EntityObject
 {
  $dataClass = $this->getDataClass();

  return $dataClass::getByPrimary($id)->fetchObject();
 }
}
