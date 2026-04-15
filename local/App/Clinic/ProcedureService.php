<?php

declare(strict_types=1);

namespace App\Clinic;

use Bitrix\Iblock\Elements\ElementProceduresTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

/**
 * Сервис для создания и удаления процедур.
 */
class ProcedureService
{
  /**
   * Создаёт новую процедуру.
   *
   * @param array<string, mixed> $data
   *
   * @return array{
   *   success: bool,
   *   id: int|null,
   *   errors: list<string>
   * }
   */
  public function create(array $data): array
  {
    $this->loadIblockModule();

    $name = trim((string)($data['name'] ?? ''));
    $description = trim((string)($data['description'] ?? ''));

    if ($name === '') {
      return [
        'success' => false,
        'id' => null,
        'errors' => [Loc::getMessage('APP_CLINIC_PROCEDURE_SERVICE_NAME_REQUIRED')],
      ];
    }

    $procedure = ElementProceduresTable::createObject();

    $procedure->set('IBLOCK_ID', $this->getProcedureIblockId());
    $procedure->set('NAME', $name);
    $procedure->set('ACTIVE', 'Y');
    $procedure->set(ClinicConfig::PROCEDURE_DESCRIPTION, $description);

    $result = $procedure->save();

    if (!$result->isSuccess()) {
      return [
        'success' => false,
        'id' => null,
        'errors' => $result->getErrorMessages(),
      ];
    }

    return [
      'success' => true,
      'id' => (int)$procedure->get('ID'),
      'errors' => [],
    ];
  }

  /**
   * Удаляет процедуру по идентификатору.
   *
   * @return array{
   *   success: bool,
   *   errors: list<string>
   * }
   */
  public function delete(int $id): array
  {
    $this->loadIblockModule();

    if ($id <= 0) {
      return [
        'success' => false,
        'errors' => [Loc::getMessage('APP_CLINIC_PROCEDURE_SERVICE_INVALID_ID')],
      ];
    }

    $result = ElementProceduresTable::delete($id);

    if (!$result->isSuccess()) {
      return [
        'success' => false,
        'errors' => $result->getErrorMessages(),
      ];
    }

    return [
      'success' => true,
      'errors' => [],
    ];
  }

  /**
   * Возвращает ID инфоблока процедур по символьному коду.
   */
  private function getProcedureIblockId(): int
  {
    $row = IblockTable::getRow([
      'select' => ['ID'],
      'filter' => ['=CODE' => ClinicConfig::PROCEDURES_IBLOCK_CODE],
    ]);

    if (!$row) {
      throw new SystemException(
        Loc::getMessage('APP_CLINIC_PROCEDURE_SERVICE_IBLOCK_NOT_FOUND')
      );
    }

    return (int)$row['ID'];
  }

  /**
   * Подключает модуль инфоблоков перед работой с ORM.
   */
  private function loadIblockModule(): void
  {
    if (!Loader::includeModule('iblock')) {
      throw new SystemException(
        Loc::getMessage('APP_CLINIC_PROCEDURE_SERVICE_IBLOCK_MODULE_REQUIRED')
      );
    }
  }
}
