<?php

require_once __DIR__ . '/PantoneColorsHelper.php';

use Bitrix\Main\Error;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\Type\Date;

class PantoneColorsValidator
{
 private PantoneColorsHelper $helper;

 public function __construct(?PantoneColorsHelper $helper = null)
 {
  $this->helper = $helper ?? new PantoneColorsHelper();
 }

 public function validateAddData(array $data): AddResult
 {
  $result = new AddResult();
  $this->validateColorFields($data, $result);

  if (!$result->isSuccess()) {
   return $result;
  }

  if ($this->colorExistsByNameAndHex($this->getName($data), $this->getHexCode($data))) {
   $result->addError(new Error('Цвет с таким названием и HEX-кодом уже существует.'));
  }

  return $result;
 }

 public function validateUpdateData(array $data): UpdateResult
 {
  $result = new UpdateResult();
  $id = (int)($data[PantoneColorsHelper::FIELD_ID] ?? 0);

  if ($id <= 0) {
   $result->addError(new Error('Не выбран цвет для редактирования.'));
  } elseif (!$this->colorExistsById($id)) {
   $result->addError(new Error('Цвет для редактирования не найден.'));
  }

  $this->validateColorFields($data, $result);

  if (!$result->isSuccess()) {
   return $result;
  }

  if ($this->colorExistsByNameAndHex($this->getName($data), $this->getHexCode($data), $id)) {
   $result->addError(new Error('Цвет с таким названием и HEX-кодом уже существует.'));
  }

  return $result;
 }

 private function validateColorFields(array $data, Result $result): void
 {
  $name = $this->getName($data);
  $hexCode = ltrim(trim((string)($data[PantoneColorsHelper::FIELD_HEX_CODE] ?? '')), '#');

  if ($name === '') {
   $result->addError(new Error('Заполните название цвета.'));
  }

  if ($hexCode === '') {
   $result->addError(new Error('Заполните HEX-код.'));
  } elseif (!preg_match('/^[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $hexCode)) {
   $result->addError(new Error('HEX-код должен быть в формате fff или ffffff.'));
  }

  $this->validateActiveFrom($data, $result);
 }

 private function validateActiveFrom(array $data, Result $result): void
 {
  $activeFrom = trim((string)($data[PantoneColorsHelper::FIELD_ACTIVE_FROM] ?? ''));

  if ($activeFrom === '') {
   return;
  }

  try {
   new Date($activeFrom, 'Y-m-d');
  } catch (\Throwable $exception) {
   $result->addError(new Error('Дата активности заполнена некорректно.'));
  }
 }

 private function getName(array $data): string
 {
  return trim((string)($data[PantoneColorsHelper::FIELD_NAME] ?? ''));
 }

 private function getHexCode(array $data): string
 {
  return mb_strtolower(ltrim(trim((string)($data[PantoneColorsHelper::FIELD_HEX_CODE] ?? '')), '#'));
 }

 private function colorExistsById(int $id): bool
 {
  $dataClass = $this->helper->getDataClass();

  return (bool)$dataClass::getList([
   'select' => [
    PantoneColorsHelper::FIELD_ID,
   ],
   'filter' => [
    '=' . PantoneColorsHelper::FIELD_ID => $id,
   ],
   'limit' => 1,
  ])->fetch();
 }

 private function colorExistsByNameAndHex(string $name, string $hexCode, ?int $excludeId = null): bool
 {
  $dataClass = $this->helper->getDataClass();
  $filter = [
   '=' . PantoneColorsHelper::FIELD_NAME => $name,
   '=' . PantoneColorsHelper::FIELD_HEX_CODE => $hexCode,
  ];

  if ($excludeId !== null) {
   $filter['!=' . PantoneColorsHelper::FIELD_ID] = $excludeId;
  }

  return (bool)$dataClass::getList([
   'select' => [
    PantoneColorsHelper::FIELD_ID,
   ],
   'filter' => $filter,
   'limit' => 1,
  ])->fetch();
 }
}
