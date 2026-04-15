<?php

declare(strict_types=1);

namespace App\Clinic;

use Bitrix\Iblock\Elements\ElementProceduresTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

/**
 * Репозиторий для чтения данных о процедурах.
 */
class ProcedureRepository
{
  /**
   * Общий набор полей для выборок процедур.
   *
   * @var array<string, string>
   */
  private const SELECT = [
    'ID',
    'NAME',
    'CODE',
    'DESCRIPTION' => ClinicConfig::PROCEDURE_DESCRIPTION_VALUE,
  ];

  /**
   * Возвращает список активных процедур.
   *
   * @return list<array{
   *   ID: string|int,
   *   NAME: string,
   *   CODE: string,
   *   DESCRIPTION: string|null
   * }>
   */
  public function getList(): array
  {
    $this->loadIblockModule();

    return ElementProceduresTable::getList([
      'select' => self::SELECT,
      'filter' => [
        '=ACTIVE' => 'Y',
      ],
      'order' => [
        'ID' => 'ASC',
      ],
    ])->fetchAll();
  }

  /**
   * Возвращает активные процедуры по списку идентификаторов.
   *
   * @param list<int|string> $ids
   *
   * @return list<array{
   *   ID: string|int,
   *   NAME: string,
   *   CODE: string,
   *   DESCRIPTION: string|null
   * }>
   */
  public function getByIds(array $ids): array
  {
    $ids = array_values(array_unique(array_filter(
      array_map('intval', $ids),
      static fn(int $id): bool => $id > 0
    )));

    if ($ids === []) {
      return [];
    }

    $this->loadIblockModule();

    return ElementProceduresTable::getList([
      'select' => self::SELECT,
      'filter' => [
        '@ID' => $ids,
        '=ACTIVE' => 'Y',
      ],
      'order' => [
        'ID' => 'ASC',
      ],
    ])->fetchAll();
  }

  /**
   * Подключает модуль инфоблоков перед выполнением ORM-запросов.
   */
  private function loadIblockModule(): void
  {
    if (!Loader::includeModule('iblock')) {
      throw new SystemException(
        Loc::getMessage('APP_CLINIC_PROCEDURE_REPOSITORY_IBLOCK_MODULE_REQUIRED')
      );
    }
  }
}
