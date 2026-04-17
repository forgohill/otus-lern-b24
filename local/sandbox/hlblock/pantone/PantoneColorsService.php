<?php

require_once __DIR__ . '/PantoneColorsHelper.php';
require_once __DIR__ . '/PantoneColorsValidator.php';

use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\DeleteResult;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\Type\Date;

class PantoneColorsService
{
 private PantoneColorsHelper $helper;
 private PantoneColorsValidator $validator;

 public function __construct(?PantoneColorsHelper $helper = null, ?PantoneColorsValidator $validator = null)
 {
  $this->helper = $helper ?? new PantoneColorsHelper();
  $this->validator = $validator ?? new PantoneColorsValidator($this->helper);
 }

 public function addMulti(array $data, bool $ignoreEvents = false): AddResult
 {
  $result = $this->validator->validateAddData($data);

  if (!$result->isSuccess()) {
   return $result;
  }

  $preparedRow = $this->prepareAddRow($data);
  $dataClass = $this->helper->getDataClass();

  return $dataClass::addMulti([$preparedRow], $ignoreEvents);
 }

 public function updateMulti(array $data, bool $ignoreEvents = false): UpdateResult
 {
  $result = $this->validator->validateUpdateData($data);

  if (!$result->isSuccess()) {
   return $result;
  }

  $id = (int)$data[PantoneColorsHelper::FIELD_ID];
  $preparedRow = $this->prepareUpdateRow($data, $id);
  $dataClass = $this->helper->getDataClass();

  return $dataClass::updateMulti([$id], $preparedRow, $ignoreEvents);
 }

 public function deleteMulti(array $ids): DeleteResult
 {
  $result = new DeleteResult();
  $ids = $this->normalizeIds($ids);

  if (!$ids) {
   $result->addError(new Error('Выберите хотя бы один цвет для удаления.'));

   return $result;
  }

  $dataClass = $this->helper->getDataClass();
  $existingIds = $this->getExistingIds($ids);
  $missingIds = array_values(array_diff($ids, $existingIds));

  if ($missingIds) {
   $result->addError(new Error('Не найдены цвета для удаления: ' . implode(', ', $missingIds) . '.'));

   return $result;
  }

  $connection = Application::getConnection();
  $connection->startTransaction();

  try {
   foreach ($ids as $id) {
    $deleteResult = $dataClass::delete($id);

    if (!$deleteResult->isSuccess()) {
     $result->addErrors($deleteResult->getErrors());

     break;
    }
   }

   if ($result->isSuccess()) {
    $connection->commitTransaction();
   } else {
    $connection->rollbackTransaction();
   }
  } catch (\Throwable $exception) {
   $connection->rollbackTransaction();

   throw $exception;
  }

  return $result;
 }

 private function prepareAddRow(array $data): array
 {
  $name = trim((string)($data[PantoneColorsHelper::FIELD_NAME] ?? ''));
  $hexCode = ltrim(trim((string)($data[PantoneColorsHelper::FIELD_HEX_CODE] ?? '')), '#');
  $hexCode = mb_strtolower($hexCode);

  $fields = [
   PantoneColorsHelper::FIELD_NAME => $name,
   PantoneColorsHelper::FIELD_HEX_CODE => $hexCode,
   PantoneColorsHelper::FIELD_XML_ID => $this->generateUniqueXmlId($name),
  ];

  $this->addOptionalStringField($fields, $data, PantoneColorsHelper::FIELD_DESCRIPTION);
  $this->addOptionalStringField($fields, $data, PantoneColorsHelper::FIELD_FULL_DESCRIPTION);
  $this->addOptionalDateField($fields, $data);
  $this->addOptionalTagsField($fields, $data);

  return $fields;
 }

 private function prepareUpdateRow(array $data, int $id): array
 {
  $name = trim((string)($data[PantoneColorsHelper::FIELD_NAME] ?? ''));
  $hexCode = ltrim(trim((string)($data[PantoneColorsHelper::FIELD_HEX_CODE] ?? '')), '#');
  $hexCode = mb_strtolower($hexCode);

  $fields = [
   PantoneColorsHelper::FIELD_NAME => $name,
   PantoneColorsHelper::FIELD_HEX_CODE => $hexCode,
   PantoneColorsHelper::FIELD_XML_ID => $this->generateUniqueXmlId($name, $id),
   PantoneColorsHelper::FIELD_DESCRIPTION => trim((string)($data[PantoneColorsHelper::FIELD_DESCRIPTION] ?? '')),
   PantoneColorsHelper::FIELD_FULL_DESCRIPTION => trim((string)($data[PantoneColorsHelper::FIELD_FULL_DESCRIPTION] ?? '')),
  ];

  $this->setDateField($fields, $data);
  $this->setTagsField($fields, $data);

  return $fields;
 }

 private function addOptionalStringField(array &$fields, array $data, string $field): void
 {
  $value = trim((string)($data[$field] ?? ''));

  if ($value !== '') {
   $fields[$field] = $value;
  }
 }

 private function addOptionalDateField(array &$fields, array $data): void
 {
  $activeFrom = trim((string)($data[PantoneColorsHelper::FIELD_ACTIVE_FROM] ?? ''));

  if ($activeFrom === '') {
   return;
  }

  $fields[PantoneColorsHelper::FIELD_ACTIVE_FROM] = new Date($activeFrom, 'Y-m-d');
 }

 private function setDateField(array &$fields, array $data): void
 {
  $activeFrom = trim((string)($data[PantoneColorsHelper::FIELD_ACTIVE_FROM] ?? ''));

  $fields[PantoneColorsHelper::FIELD_ACTIVE_FROM] = $activeFrom === '' ? null : new Date($activeFrom, 'Y-m-d');
 }

 private function addOptionalTagsField(array &$fields, array $data): void
 {
  $tags = $data[PantoneColorsHelper::FIELD_TAGS] ?? null;
  if (is_string($tags)) {
   $tags = explode(',', $tags);
  }

  $tags = $this->normalizeTags($tags);
  if ($tags) {
   $fields[PantoneColorsHelper::FIELD_TAGS] = $tags;
  }
 }

 private function setTagsField(array &$fields, array $data): void
 {
  $tags = $data[PantoneColorsHelper::FIELD_TAGS] ?? null;
  if (is_string($tags)) {
   $tags = explode(',', $tags);
  }

  $fields[PantoneColorsHelper::FIELD_TAGS] = $this->normalizeTags($tags);
 }

 private function normalizeTags($tags): array
 {
  if (!is_array($tags)) {
   $tags = [$tags];
  }

  return array_values(array_filter($tags, static function ($tag): bool {
   return $tag !== null && $tag !== '';
  }));
 }

 private function normalizeIds(array $ids): array
 {
  $ids = array_map('intval', $ids);
  $ids = array_filter($ids, static function (int $id): bool {
   return $id > 0;
  });

  return array_values(array_unique($ids));
 }

 private function getExistingIds(array $ids): array
 {
  if (!$ids) {
   return [];
  }

  $dataClass = $this->helper->getDataClass();
  $rows = $dataClass::getList([
   'select' => [
    PantoneColorsHelper::FIELD_ID,
   ],
   'filter' => [
    '=' . PantoneColorsHelper::FIELD_ID => $ids,
   ],
  ])->fetchAll();

  return array_map('intval', array_column($rows, PantoneColorsHelper::FIELD_ID));
 }

 private function generateUniqueXmlId(string $name, ?int $excludeId = null): string
 {
  $baseXmlId = (string)\CUtil::translit($name, 'ru', [
   'max_len' => 255,
   'change_case' => 'L',
   'replace_space' => '-',
   'replace_other' => '-',
   'delete_repeat_replace' => true,
   'safe_chars' => '',
  ]);
  $baseXmlId = trim($baseXmlId, '-');

  if ($baseXmlId === '') {
   $baseXmlId = 'pantone-color';
  }

  $xmlId = $baseXmlId;
  $index = 1;

  while ($this->xmlIdExists($xmlId, $excludeId)) {
   $xmlId = $baseXmlId . '-' . str_pad((string)$index, 3, '0', STR_PAD_LEFT);
   $index++;
  }

  return $xmlId;
 }

 private function xmlIdExists(string $xmlId, ?int $excludeId = null): bool
 {
  if ($xmlId === '') {
   return false;
  }

  $dataClass = $this->helper->getDataClass();
  $filter = [
   '=' . PantoneColorsHelper::FIELD_XML_ID => $xmlId,
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
