<?php

declare(strict_types=1);

const HOMEWORK3_DEMO_MODE = true;

function homework3GetDemoNotice(): string
{
  return 'Страница работает в демо-режиме. Здесь временные заглушки, чтобы можно было спокойно переделывать проект без рабочего backend.';
}

function homework3GetDemoSubmitNotice(): string
{
  return 'Демо-режим: сохранение пока отключено. Можно менять вёрстку, тексты и поля без риска уронить страницу.';
}

function homework3GetDemoDoctors(): array
{
  return [
    [
      'id' => 101,
      'last_name' => 'Иванова',
      'first_name' => 'Анна',
      'middle_name' => 'Сергеевна',
      'birth_date' => '',
      'individual_tax_number' => '',
      'procedure_ids' => [201, 202],
    ],
    [
      'id' => 102,
      'last_name' => 'Петров',
      'first_name' => 'Максим',
      'middle_name' => 'Олегович',
      'birth_date' => '',
      'individual_tax_number' => '',
      'procedure_ids' => [203],
    ],
    [
      'id' => 103,
      'last_name' => 'Соколова',
      'first_name' => 'Мария',
      'middle_name' => 'Ильинична',
      'birth_date' => '',
      'individual_tax_number' => '',
      'procedure_ids' => [201, 204],
    ],
  ];
}

function homework3GetDemoProcedureMap(): array
{
  return [
    201 => [
      'id' => 201,
      'name' => 'Первичный приём',
      'description' => 'Демо-описание процедуры для главной страницы и карточки врача.',
    ],
    202 => [
      'id' => 202,
      'name' => 'Повторная консультация',
      'description' => 'Повторный визит пациента с коротким текстом-заглушкой.',
    ],
    203 => [
      'id' => 203,
      'name' => 'УЗИ',
      'description' => 'Пример процедуры без реальной привязки к инфоблоку.',
    ],
    204 => [
      'id' => 204,
      'name' => 'Анализ результатов',
      'description' => 'Временная процедура, чтобы список на странице не был пустым.',
    ],
  ];
}

function homework3BuildDoctorFullName(array $doctor): string
{
  return trim(
    (string)($doctor['last_name'] ?? $doctor['LAST_NAME'] ?? '') . ' ' .
      (string)($doctor['first_name'] ?? $doctor['FIRST_NAME'] ?? '') . ' ' .
      (string)($doctor['middle_name'] ?? $doctor['MIDDLE_NAME'] ?? '')
  );
}

function homework3GetDoctorList(): array
{
  return array_map(
    static function (array $doctor): array {
      return [
        'ID' => (int)$doctor['id'],
        'LAST_NAME' => (string)$doctor['last_name'],
        'FIRST_NAME' => (string)$doctor['first_name'],
        'MIDDLE_NAME' => (string)$doctor['middle_name'],
        'BIRTH_DATE' => (string)$doctor['birth_date'],
        'INDIVIDUAL_TAX_NUMBER' => (string)$doctor['individual_tax_number'],
        'INN' => (string)$doctor['individual_tax_number'],
        'PROCEDURE_IDS' => $doctor['procedure_ids'],
      ];
    },
    homework3GetDemoDoctors()
  );
}

function homework3FindDoctor(int $doctorId): array
{
  $doctors = homework3GetDemoDoctors();

  foreach ($doctors as $doctor) {
    if ((int)$doctor['id'] === $doctorId) {
      return $doctor;
    }
  }

  return $doctors[0] ?? [];
}

function homework3GetDoctorCardData(int $doctorId): array
{
  $doctor = homework3FindDoctor($doctorId);

  if ($doctor === []) {
    return [];
  }

  return [
    'id' => (int)$doctor['id'],
    'full_name' => homework3BuildDoctorFullName($doctor),
    'last_name' => (string)$doctor['last_name'],
    'first_name' => (string)$doctor['first_name'],
    'middle_name' => (string)$doctor['middle_name'],
    'birth_date' => (string)$doctor['birth_date'],
    'individual_tax_number' => (string)$doctor['individual_tax_number'],
    'procedure_ids' => $doctor['procedure_ids'],
  ];
}

function homework3GetDoctorForEdit(int $doctorId): array
{
  $doctor = homework3FindDoctor($doctorId);

  if ($doctor === []) {
    return homework3GetDoctorDraftFormData();
  }

  return [
    'LAST_NAME' => (string)$doctor['last_name'],
    'FIRST_NAME' => (string)$doctor['first_name'],
    'MIDDLE_NAME' => (string)$doctor['middle_name'],
    'BIRTH_DATE' => (string)$doctor['birth_date'],
    'INN' => (string)$doctor['individual_tax_number'],
    'PROCEDURES' => array_map('intval', $doctor['procedure_ids']),
  ];
}

function homework3GetDoctorDraftFormData(): array
{
  return [
    'LAST_NAME' => '',
    'FIRST_NAME' => '',
    'MIDDLE_NAME' => '',
    'BIRTH_DATE' => '',
    'INN' => '',
    'PROCEDURES' => [201],
  ];
}

function homework3GetProcedureNames(): array
{
  $result = [];

  foreach (homework3GetDemoProcedureMap() as $procedureId => $procedure) {
    $result[(int)$procedureId] = (string)$procedure['name'];
  }

  return $result;
}

function homework3GetProcedureRows(): array
{
  return array_values(homework3GetDemoProcedureMap());
}

function homework3GetProceduresByIds(array $ids): array
{
  $procedureMap = homework3GetDemoProcedureMap();
  $result = [];

  foreach ($ids as $id) {
    $id = (int)$id;

    if ($id > 0 && isset($procedureMap[$id])) {
      $result[] = $procedureMap[$id];
    }
  }

  return $result;
}
