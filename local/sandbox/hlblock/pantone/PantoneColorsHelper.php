<?php

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;

class PantoneColorsHelper
{
 private const HL_BLOCK_NAME = 'PantoneColors';

 public const FIELD_ID = 'ID';
 public const FIELD_NAME = 'UF_NAME';
 public const FIELD_ACTIVE_FROM = 'UF_ACTIVE_FROM';
 public const FIELD_XML_ID = 'UF_XML_ID';
 public const FIELD_TAGS = 'UF_TAGS';
 public const FIELD_HEX_CODE = 'UF_HEX_CODE';
 public const FIELD_DESCRIPTION = 'UF_DESCRIPTION';
 public const FIELD_FULL_DESCRIPTION = 'UF_FULL_DESCRIPTION';

 private ?string $dataClass = null;

 public function getDataClass(): string
 {
  if ($this->dataClass !== null) {
   return $this->dataClass;
  }
  Loader::includeModule('highloadblock');

  $hlblock = HighloadBlockTable::getList([
   'filter' => [
    '=NAME' => self::HL_BLOCK_NAME,
   ],
   'limit' => 1,
  ])->fetch();


  if (!$hlblock) {
   throw new \RuntimeException('HL-блок PantoneColors не найден');
  }

  $entity = HighloadBlockTable::compileEntity($hlblock);

  $this->dataClass = $entity->getDataClass();

  return $this->dataClass;
 }

 public function getObjectById(int $id): ?object
 {
  $dataClass = $this->getDataClass();

  return $dataClass::getList([
   'select' => [
    self::FIELD_ID,
    self::FIELD_NAME,
    self::FIELD_ACTIVE_FROM,
    self::FIELD_HEX_CODE,
    self::FIELD_XML_ID,
    self::FIELD_TAGS,
    self::FIELD_DESCRIPTION,
    self::FIELD_FULL_DESCRIPTION,
   ],
   'filter' => [
    '=ID' => $id,
   ],
   'limit' => 1,
  ])->fetchObject();
 }

 public function getCollection(): object
 {
  $dataClass = $this->getDataClass();

  return $dataClass::getList([
   'select' => [
    self::FIELD_ID,
    self::FIELD_NAME,
    self::FIELD_ACTIVE_FROM,
    self::FIELD_HEX_CODE,
    self::FIELD_XML_ID,
    self::FIELD_TAGS,
    self::FIELD_DESCRIPTION,
    self::FIELD_FULL_DESCRIPTION,
   ],
   'order' => [
    self::FIELD_ID => 'ASC',
   ],
  ])->fetchCollection();
 }

 public function getPreparedItems(): array
 {
  $items = [];

  foreach ($this->getCollection() as $color) {
   $hexCode = (string)$color->get(self::FIELD_HEX_CODE);
   $activeFrom = $color->get(self::FIELD_ACTIVE_FROM);
   $tags = $color->get(self::FIELD_TAGS);

   $items[] = [
    'id' => $color->getId(),
    'name' => (string)$color->get(self::FIELD_NAME),
    'xml_id' => (string)$color->get(self::FIELD_XML_ID),
    'hex_code' => $hexCode,
    'background_color' => '#' . ltrim($hexCode, '#'),
    'active_from' => $activeFrom ? $activeFrom->format('d.m.Y') : '',
    'active_from_input' => $activeFrom ? $activeFrom->format('Y-m-d') : '',
    'tags' => $this->normalizeTags($tags),
    'description' => (string)$color->get(self::FIELD_DESCRIPTION),
    'full_description' => (string)$color->get(self::FIELD_FULL_DESCRIPTION),
   ];
  }

  return $items;
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
}
