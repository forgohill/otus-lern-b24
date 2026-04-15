<?php

declare(strict_types=1);

namespace App\Clinic;

use Bitrix\Iblock\Elements\ElementDoctorsTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\ORM\PropertyValue;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

/**
 * Сервис для создания, обновления и удаления врачей.
 */
class DoctorService
{
  /**
   * Создаёт врача и при необходимости связывает его с процедурами.
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

    $doctorData = $this->extractDoctorData($data);
    $errors = $this->validateDoctorData($doctorData);

    if ($errors !== []) {
      return [
        'success' => false,
        'id' => null,
        'errors' => $errors,
      ];
    }

    $doctor = ElementDoctorsTable::createObject();

    $this->assignIblockId($doctor);
    $this->fillDoctorFields($doctor, $doctorData);

    $result = $doctor->save();

    if (!$result->isSuccess()) {
      return [
        'success' => false,
        'id' => null,
        'errors' => $this->extractOrmErrors(
          $result,
          Loc::getMessage('APP_CLINIC_DOCTOR_SERVICE_CREATE_ERROR')
        ),
      ];
    }

    $doctorId = (int)$doctor->get('ID');

    if ($doctorData['procedure_ids'] !== []) {
      $syncResult = $this->syncDoctorProcedures($doctorId, $doctorData['procedure_ids']);

      if (!$syncResult['success']) {
        ElementDoctorsTable::delete($doctorId);

        return [
          'success' => false,
          'id' => null,
          'errors' => $syncResult['errors'],
        ];
      }
    }

    return [
      'success' => true,
      'id' => $doctorId,
      'errors' => [],
    ];
  }

  /**
   * Обновляет данные врача и заменяет список связанных процедур.
   *
   * @param array<string, mixed> $data
   *
   * @return array{
   *   success: bool,
   *   id: int|null,
   *   errors: list<string>
   * }
   */
  public function update(int $id, array $data): array
  {
    $this->loadIblockModule();

    if ($id <= 0) {
      return [
        'success' => false,
        'id' => null,
        'errors' => [Loc::getMessage('APP_CLINIC_DOCTOR_SERVICE_INVALID_ID')],
      ];
    }

    $doctorData = $this->extractDoctorData($data);
    $errors = $this->validateDoctorData($doctorData);

    if ($errors !== []) {
      return [
        'success' => false,
        'id' => null,
        'errors' => $errors,
      ];
    }

    $doctor = $this->loadDoctorForSave($id);

    if ($doctor === null) {
      return [
        'success' => false,
        'id' => null,
        'errors' => [Loc::getMessage('APP_CLINIC_DOCTOR_SERVICE_NOT_FOUND')],
      ];
    }

    $this->fillDoctorFields($doctor, $doctorData);
    $this->replaceMultiplePropertyValues(
      $doctor,
      ClinicConfig::DOCTOR_PROCEDURE_IDS,
      $doctorData['procedure_ids']
    );

    $result = $doctor->save();

    if (!$result->isSuccess()) {
      return [
        'success' => false,
        'id' => null,
        'errors' => $this->extractOrmErrors(
          $result,
          Loc::getMessage('APP_CLINIC_DOCTOR_SERVICE_UPDATE_ERROR')
        ),
      ];
    }

    return [
      'success' => true,
      'id' => $id,
      'errors' => [],
    ];
  }

  /**
   * Удаляет врача по идентификатору.
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
        'errors' => [Loc::getMessage('APP_CLINIC_DOCTOR_SERVICE_INVALID_ID')],
      ];
    }

    $result = ElementDoctorsTable::delete($id);

    if (!$result->isSuccess()) {
      return [
        'success' => false,
        'errors' => $this->extractOrmErrors(
          $result,
          Loc::getMessage('APP_CLINIC_DOCTOR_SERVICE_DELETE_ERROR')
        ),
      ];
    }

    return [
      'success' => true,
      'errors' => [],
    ];
  }

  /**
   * Нормализует входные данные формы врача.
   *
   * @param array<string, mixed> $data
   *
   * @return array{
   *   last_name: string,
   *   first_name: string,
   *   middle_name: string,
   *   procedure_ids: list<int>
   * }
   */
  private function extractDoctorData(array $data): array
  {
    return [
      'last_name' => trim((string)($data['last_name'] ?? '')),
      'first_name' => trim((string)($data['first_name'] ?? '')),
      'middle_name' => trim((string)($data['middle_name'] ?? '')),
      'procedure_ids' => $this->normalizeProcedureIds($data['procedure_ids'] ?? []),
    ];
  }

  /**
   * Проверяет обязательные поля врача.
   *
   * @param array{
   *   last_name: string,
   *   first_name: string,
   *   middle_name: string,
   *   procedure_ids: list<int>
   * } $doctorData
   *
   * @return list<string>
   */
  private function validateDoctorData(array $doctorData): array
  {
    $errors = [];

    if ($doctorData['last_name'] === '') {
      $errors[] = Loc::getMessage('APP_CLINIC_DOCTOR_SERVICE_LAST_NAME_REQUIRED');
    }

    if ($doctorData['first_name'] === '') {
      $errors[] = Loc::getMessage('APP_CLINIC_DOCTOR_SERVICE_FIRST_NAME_REQUIRED');
    }

    return $errors;
  }

  /**
   * Заполняет ORM-объект врача основными полями и одиночными свойствами.
   *
   * @param array{
   *   last_name: string,
   *   first_name: string,
   *   middle_name: string,
   *   procedure_ids: list<int>
   * } $doctorData
   */
  private function fillDoctorFields(EntityObject $doctor, array $doctorData): void
  {
    $doctor->set(
      'NAME',
      $this->buildName(
        $doctorData['last_name'],
        $doctorData['first_name'],
        $doctorData['middle_name']
      )
    );
    $doctor->set('ACTIVE', 'Y');
    $doctor->set(ClinicConfig::DOCTOR_LAST_NAME, $doctorData['last_name']);
    $doctor->set(ClinicConfig::DOCTOR_FIRST_NAME, $doctorData['first_name']);
    $doctor->set(
      ClinicConfig::DOCTOR_MIDDLE_NAME,
      $doctorData['middle_name'] !== '' ? $doctorData['middle_name'] : null
    );
  }

  /**
   * Строит символьный код элемента на основе ФИО врача.
   */
  private function buildName(
    string $lastName,
    string $firstName,
    string $middleName
  ): string {
    $fullName = trim(implode(' ', array_filter([
      $lastName,
      $firstName,
      $middleName,
    ])));

    return (string)\CUtil::translit($fullName, 'ru', [
      'max_len' => 255,
      'change_case' => 'L',
      'replace_space' => '-',
      'replace_other' => '-',
      'delete_repeat_replace' => 'true',
      'safe_chars' => '',
    ]);
  }

  /**
   * Сохраняет множественное свойство процедур у врача.
   *
   * @param list<int> $procedureIds
   *
   * @return array{
   *   success: bool,
   *   errors: list<string>
   * }
   */
  private function syncDoctorProcedures(int $doctorId, array $procedureIds): array
  {
    $doctor = $this->loadDoctorForSave($doctorId);

    if ($doctor === null) {
      return [
        'success' => false,
        'errors' => [Loc::getMessage('APP_CLINIC_DOCTOR_SERVICE_LOAD_FOR_PROCEDURES_ERROR')],
      ];
    }

    $this->replaceMultiplePropertyValues(
      $doctor,
      ClinicConfig::DOCTOR_PROCEDURE_IDS,
      $procedureIds
    );

    $result = $doctor->save();

    if (!$result->isSuccess()) {
      return [
        'success' => false,
        'errors' => $this->extractOrmErrors(
          $result,
          Loc::getMessage('APP_CLINIC_DOCTOR_SERVICE_SAVE_PROCEDURES_ERROR')
        ),
      ];
    }

    return [
      'success' => true,
      'errors' => [],
    ];
  }

  /**
   * Загружает ORM-объект врача для сохранения.
   */
  private function loadDoctorForSave(int $doctorId): ?EntityObject
  {
    return ElementDoctorsTable::getByPrimary($doctorId, [
      'select' => [
        'ID',
        'NAME',
        'ACTIVE',
        ClinicConfig::DOCTOR_LAST_NAME,
        ClinicConfig::DOCTOR_FIRST_NAME,
        ClinicConfig::DOCTOR_MIDDLE_NAME,
        ClinicConfig::DOCTOR_PROCEDURE_IDS,
      ],
    ])->fetchObject();
  }

  /**
   * Полностью заменяет значения множественного свойства инфоблока.
   *
   * @param list<int> $values
   */
  private function replaceMultiplePropertyValues(
    EntityObject $doctor,
    string $propertyCode,
    array $values
  ): void {
    $doctor->removeAll($propertyCode);

    foreach ($values as $value) {
      $doctor->addTo($propertyCode, new PropertyValue($value));
    }
  }

  /**
   * Оставляет только уникальные положительные ID процедур.
   *
   * @return list<int>
   */
  private function normalizeProcedureIds(mixed $value): array
  {
    if (!is_array($value)) {
      return [];
    }

    $result = [];

    foreach ($value as $procedureId) {
      $procedureId = (int)$procedureId;

      if ($procedureId > 0) {
        $result[] = $procedureId;
      }
    }

    return array_values(array_unique($result));
  }

  /**
   * Назначает инфоблок ORM-объекту врача перед сохранением.
   */
  private function assignIblockId(object $doctor): void
  {
    $iblockId = $this->getDoctorsIblockId();

    if (method_exists($doctor, 'setIblockId')) {
      $doctor->setIblockId($iblockId);

      return;
    }

    $doctor->set('IBLOCK_ID', $iblockId);
  }

  /**
   * Возвращает ID инфоблока врачей по символьному коду.
   */
  private function getDoctorsIblockId(): int
  {
    $row = IblockTable::getRow([
      'select' => ['ID'],
      'filter' => ['=CODE' => ClinicConfig::IBLOCK_DOCTORS],
    ]);

    if (!$row) {
      throw new SystemException(
        Loc::getMessage('APP_CLINIC_DOCTOR_SERVICE_IBLOCK_NOT_FOUND')
      );
    }

    return (int)$row['ID'];
  }

  /**
   * Нормализует ошибки ORM-результата в непустой список сообщений.
   *
   * @return list<string>
   */
  private function extractOrmErrors(Result $result, string $defaultMessage): array
  {
    $errors = array_values(array_filter(
      array_map('trim', $result->getErrorMessages()),
      static fn(string $error): bool => $error !== ''
    ));

    return $errors !== [] ? $errors : [$defaultMessage];
  }

  /**
   * Подключает модуль инфоблоков перед работой с ORM.
   */
  private function loadIblockModule(): void
  {
    if (!Loader::includeModule('iblock')) {
      throw new SystemException(
        Loc::getMessage('APP_CLINIC_DOCTOR_SERVICE_IBLOCK_MODULE_REQUIRED')
      );
    }
  }
}
