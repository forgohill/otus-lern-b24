<?php

declare(strict_types=1);

namespace App\Clinic;

use App\Debug\Log;
use Bitrix\Iblock\Elements\ElementDoctorsTable;
use Bitrix\Iblock\Elements\ElementProceduresTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

/**
 * Репозиторий для чтения данных о врачах и их связанных процедурах.
 */
class DoctorRepository
{
  /**
   * Возвращает список активных врачей для общего списка.
   *
   * @return list<array{
   *   id: int,
   *   last_name: string,
   *   first_name: string,
   *   middle_name: string,
   *   full_name: string
   * }>
   */
  public function getList(): array
  {
    $this->loadIblockModule();

    $rows = ElementDoctorsTable::getList([
      'select' => [
        'ID',
        'LAST_NAME_VALUE' => $this->buildPropertyValueSelect(ClinicConfig::DOCTOR_LAST_NAME),
        'FIRST_NAME_VALUE' => $this->buildPropertyValueSelect(ClinicConfig::DOCTOR_FIRST_NAME),
        'MIDDLE_NAME_VALUE' => $this->buildPropertyValueSelect(ClinicConfig::DOCTOR_MIDDLE_NAME),
      ],
      'filter' => [
        '=ACTIVE' => 'Y',
      ],
      'order' => [
        'ID' => 'ASC',
      ],
    ])->fetchAll();

    $result = [];

    foreach ($rows as $row) {
      $lastName = trim((string)($row['LAST_NAME_VALUE'] ?? ''));
      $firstName = trim((string)($row['FIRST_NAME_VALUE'] ?? ''));
      $middleName = trim((string)($row['MIDDLE_NAME_VALUE'] ?? ''));

      $result[] = $this->buildDoctorData(
        (int)($row['ID'] ?? 0),
        $lastName,
        $firstName,
        $middleName
      );
    }

    return $result;
  }

  /**
   * Возвращает врача по идентификатору вместе со списком ID его процедур.
   *
   * @return array{
   *   id: int,
   *   last_name: string,
   *   first_name: string,
   *   middle_name: string,
   *   full_name: string,
   *   procedure_ids: list<int>
   * }|null
   */
  public function getById(int $id): ?array
  {
    $doctor = $this->getDoctorObject($id);

    if ($doctor === null) {
      return null;
    }

    $lastName = $this->extractSinglePropertyValue(
      $doctor->get(ClinicConfig::DOCTOR_LAST_NAME)
    ) ?? '';

    $firstName = $this->extractSinglePropertyValue(
      $doctor->get(ClinicConfig::DOCTOR_FIRST_NAME)
    ) ?? '';

    $middleName = $this->extractSinglePropertyValue(
      $doctor->get(ClinicConfig::DOCTOR_MIDDLE_NAME)
    ) ?? '';

    return $this->buildDoctorData(
      (int)$doctor->get('ID'),
      $lastName,
      $firstName,
      $middleName,
      $this->extractMultipleIntPropertyValues(
        $doctor->get(ClinicConfig::DOCTOR_PROCEDURE_IDS)
      )
    );
  }

  /**
   * Возвращает данные врача для детального просмотра вместе с описанием процедур.
   *
   * @return array{
   *   id: int,
   *   last_name: string,
   *   first_name: string,
   *   middle_name: string,
   *   full_name: string,
   *   procedure_ids: list<int>,
   *   procedures: list<array{
   *     ID: int,
   *     NAME: string,
   *     CODE: string,
   *     DESCRIPTION: string
   *   }>
   * }|null
   */
  public function getViewData(int $id): ?array
  {
    $doctor = $this->getById($id);

    if ($doctor === null) {
      return null;
    }

    $query = ElementDoctorsTable::query();
    $query->setSelect([
      'PROCEDURE_ID' => 'PROCEDURE.ID',
      'PROCEDURE_NAME' => 'PROCEDURE.NAME',
      'PROCEDURE_CODE' => 'PROCEDURE.CODE',
      'PROCEDURE_DESCRIPTION' => 'PROCEDURE.' . ClinicConfig::PROCEDURE_DESCRIPTION_VALUE,
      'PROCEDURE_ACTIVE' => 'PROCEDURE.ACTIVE',
    ]);
    $query->setFilter([
      '=ID' => $id,
    ]);
    $query->setOrder([
      'PROCEDURE.ID' => 'ASC',
    ]);
    $query->registerRuntimeField(
      new Reference(
        'PROCEDURE',
        ElementProceduresTable::class,
        [
          '=this.' . $this->buildPropertyValueSelect(ClinicConfig::DOCTOR_PROCEDURE_IDS) => 'ref.ID',
        ],
        [
          'join_type' => 'left',
        ]
      )
    );

    $procedureRows = $query->fetchAll();

    Log::addLog([
      'step' => 'local/App/Clinic/DoctorRepository.php',
      'procedureRows' => $procedureRows,
    ], true, 'debug_result', true);

    $procedures = [];

    foreach ($procedureRows as $row) {
      $procedureId = (int)($row['PROCEDURE_ID'] ?? 0);

      if ($procedureId <= 0) {
        continue;
      }

      if (($row['PROCEDURE_ACTIVE'] ?? 'N') !== 'Y') {
        continue;
      }

      if (isset($procedures[$procedureId])) {
        continue;
      }

      $procedures[$procedureId] = [
        'ID' => $procedureId,
        'NAME' => (string)($row['PROCEDURE_NAME'] ?? ''),
        'CODE' => (string)($row['PROCEDURE_CODE'] ?? ''),
        'DESCRIPTION' => (string)($row['PROCEDURE_DESCRIPTION'] ?? ''),
      ];
    }

    $doctor['procedures'] = array_values($procedures);

    return $doctor;
  }

  /**
   * Загружает ORM-объект врача со свойствами, необходимыми для чтения карточки.
   */
  private function getDoctorObject(int $id): ?EntityObject
  {
    if ($id <= 0) {
      return null;
    }

    $this->loadIblockModule();

    return ElementDoctorsTable::getByPrimary($id, [
      'select' => [
        'ID',
        $this->buildPropertyValueSelect(ClinicConfig::DOCTOR_LAST_NAME),
        $this->buildPropertyValueSelect(ClinicConfig::DOCTOR_FIRST_NAME),
        $this->buildPropertyValueSelect(ClinicConfig::DOCTOR_MIDDLE_NAME),
        $this->buildPropertyValueSelect(ClinicConfig::DOCTOR_PROCEDURE_IDS),
      ],
    ])->fetchObject();
  }

  /**
   * Подключает модуль инфоблоков перед выполнением ORM-запросов.
   */
  private function loadIblockModule(): void
  {
    if (!Loader::includeModule('iblock')) {
      throw new SystemException(
        Loc::getMessage('APP_CLINIC_DOCTOR_REPOSITORY_IBLOCK_MODULE_REQUIRED')
      );
    }
  }

  /**
   * Формирует путь к значению свойства в ORM-селекте.
   */
  private function buildPropertyValueSelect(string $propertyCode): string
  {
    return $propertyCode . '.VALUE';
  }

  /**
   * Собирает нормализованный массив данных врача.
   *
   * @param list<int>|null $procedureIds
   *
   * @return array{
   *   id: int,
   *   last_name: string,
   *   first_name: string,
   *   middle_name: string,
   *   full_name: string,
   *   procedure_ids?: list<int>
   * }
   */
  private function buildDoctorData(
    int $id,
    string $lastName,
    string $firstName,
    string $middleName,
    ?array $procedureIds = null
  ): array {
    $doctor = [
      'id' => $id,
      'last_name' => $lastName,
      'first_name' => $firstName,
      'middle_name' => $middleName,
      'full_name' => $this->buildFullName($lastName, $firstName, $middleName),
    ];

    if ($procedureIds !== null) {
      $doctor['procedure_ids'] = $procedureIds;
    }

    return $doctor;
  }

  /**
   * Извлекает одиночное строковое значение свойства из разных ORM-представлений.
   */
  private function extractSinglePropertyValue(mixed $propertyValue): ?string
  {
    if ($propertyValue === null) {
      return null;
    }

    if (is_scalar($propertyValue)) {
      return $this->normalizeNullableString($propertyValue);
    }

    if (is_object($propertyValue)) {
      if (method_exists($propertyValue, 'getValue')) {
        return $this->normalizeNullableString($propertyValue->getValue());
      }

      if (method_exists($propertyValue, 'get')) {
        return $this->normalizeNullableString($propertyValue->get('VALUE'));
      }
    }

    return null;
  }

  /**
   * Извлекает и нормализует набор целочисленных значений множественного свойства.
   *
   * @return list<int>
   */
  private function extractMultipleIntPropertyValues(mixed $propertyValue): array
  {
    if ($propertyValue === null) {
      return [];
    }

    $result = [];

    if (is_scalar($propertyValue)) {
      $this->appendIntValue($result, $propertyValue);

      return array_values(array_unique($result));
    }

    if (is_object($propertyValue) && method_exists($propertyValue, 'getAll')) {
      $propertyValue = $propertyValue->getAll();
    }

    if (is_iterable($propertyValue)) {
      foreach ($propertyValue as $item) {
        $this->appendIntValue($result, $item);
      }
    } else {
      $this->appendIntValue($result, $propertyValue);
    }

    return array_values(array_unique($result));
  }

  /**
   * Добавляет в результирующий массив положительное целое значение свойства.
   *
   * @param list<int> &$result
   */
  private function appendIntValue(array &$result, mixed $value): void
  {
    if (is_scalar($value)) {
      $intValue = (int)$value;

      if ($intValue > 0) {
        $result[] = $intValue;
      }

      return;
    }

    if (!is_object($value)) {
      return;
    }

    $rawValue = null;

    if (method_exists($value, 'getValue')) {
      $rawValue = $value->getValue();
    } elseif (method_exists($value, 'get')) {
      $rawValue = $value->get('VALUE');
    }

    $intValue = (int)$rawValue;

    if ($intValue > 0) {
      $result[] = $intValue;
    }
  }

  /**
   * Приводит значение к непустой строке или null.
   */
  private function normalizeNullableString(mixed $value): ?string
  {
    if ($value === null) {
      return null;
    }

    $value = trim((string)$value);

    return $value !== '' ? $value : null;
  }

  /**
   * Собирает полное имя врача из отдельных частей.
   */
  private function buildFullName(
    string $lastName,
    string $firstName,
    string $middleName
  ): string {
    return trim(implode(' ', array_filter([
      $lastName,
      $firstName,
      $middleName,
    ])));
  }
}
