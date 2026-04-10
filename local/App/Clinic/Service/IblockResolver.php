<?php

namespace App\Clinic\Service;

use App\Debug\Log;
use Bitrix\Iblock\IblockTable;

class IblockResolver
{
 private const LOG_FILE = 'clinic_code';

 private array $cache = [];

 /**
  * Возвращает ID инфоблока по его символьному коду.
  *
  * @param string $iblockCode Символьный код инфоблока.
  * @return int ID инфоблока или 0, если инфоблок не найден.
  */
 public function getIdByCode(string $iblockCode): int
 {
  if (isset($this->cache[$iblockCode])) {
   return $this->cache[$iblockCode];
  }

  $row = IblockTable::getList([
   'select' => ['ID', 'CODE'],
   'filter' => ['=CODE' => $iblockCode],
   'limit' => 1,
  ])->fetch();

  $id = (int)($row['ID'] ?? 0);
  $this->cache[$iblockCode] = $id;

  Log::addLog([
   'step' => 'resolve_iblock_id_by_code',
   'iblock_code' => $iblockCode,
   'resolved_id' => $id,
  ], false, self::LOG_FILE, true);

  return $id;
 }
}
