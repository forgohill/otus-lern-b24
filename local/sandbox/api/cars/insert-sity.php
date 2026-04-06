<?php
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;
use App\Debug\Log;

if (!Loader::includeModule('iblock')) {
 die('Ошибка: модуль iblock не подключен');
}

if (!class_exists(Log::class)) {
 die('Ошибка: класс App\\Debug\\Log не найден');
}

$iblockId = 18;
$propEnId = 69;
$propDeId = 70;
$logFileName = 'cities_import';

$rows = <<<TXT
Русский | English | Deutsch
Берлин | Berlin | Berlin
Прага | Prague | Prag
Мюнхен | Munich | München
Вена | Vienna | Wien
Будапешт | Budapest | Budapest
Токио | Tokyo | Tokio
Варшава | Warsaw | Warschau
Братислава | Bratislava | Bratislava
Брно | Brno | Brünn
Сеул | Seoul | Seoul
Осака | Osaka | Osaka
Рим | Rome | Rom
TXT;

function normalizeValue($value): string
{
 return trim((string)$value);
}

function getPropertyValueById(int $iblockId, int $elementId, int $propertyId): string
{
 $property = CIBlockElement::GetProperty(
  $iblockId,
  $elementId,
  ['sort' => 'asc'],
  ['ID' => $propertyId]
 )->Fetch();

 return isset($property['VALUE']) ? trim((string)$property['VALUE']) : '';
}

function logImport(array $data, bool $clear = false, string $fileName = 'cities_import'): void
{
 Log::addLog($data, $clear, $fileName, true);
}

$iblock = CIBlock::GetByID($iblockId)->Fetch();
if (!$iblock) {
 logImport([
  'status' => 'fatal_error',
  'message' => 'Инфоблок не найден',
  'iblock_id' => $iblockId,
 ], true, $logFileName);

 die('Ошибка: инфоблок с ID ' . $iblockId . ' не найден');
}

$propEn = CIBlockProperty::GetByID($propEnId, $iblockId)->Fetch();
$propDe = CIBlockProperty::GetByID($propDeId, $iblockId)->Fetch();

if (!$propEn || !$propDe) {
 logImport([
  'status' => 'fatal_error',
  'message' => 'Не найдены свойства инфоблока',
  'iblock_id' => $iblockId,
  'prop_en_id' => $propEnId,
  'prop_de_id' => $propDeId,
  'prop_en_found' => (bool)$propEn,
  'prop_de_found' => (bool)$propDe,
 ], true, $logFileName);

 die('Ошибка: одно или оба свойства не найдены');
}

$lines = preg_split('/\R/u', trim($rows));
$element = new CIBlockElement();

$stats = [
 'total_lines' => count($lines),
 'processed' => 0,
 'added' => 0,
 'updated' => 0,
 'skipped' => 0,
 'errors' => 0,
 'verified_ok' => 0,
 'verified_fail' => 0,
];

logImport([
 'status' => 'start',
 'message' => 'Запуск импорта городов',
 'iblock_id' => $iblockId,
 'iblock_name' => $iblock['NAME'],
 'prop_en' => [
  'id' => $propEnId,
  'name' => $propEn['NAME'],
  'code' => $propEn['CODE'],
 ],
 'prop_de' => [
  'id' => $propDeId,
  'name' => $propDe['NAME'],
  'code' => $propDe['CODE'],
 ],
 'rows_count' => count($lines),
], true, $logFileName);

echo '<pre>';

foreach ($lines as $lineNumber => $line) {
 $line = trim((string)$line);

 if ($line === '' || mb_substr($line, 0, 1) === '#') {
  $stats['skipped']++;
  continue;
 }

 try {
  $parts = preg_split('/\s*\|\s*/u', $line);

  if (count($parts) < 3) {
   $stats['errors']++;

   $errorData = [
    'status' => 'row_error',
    'line_number' => $lineNumber + 1,
    'source_line' => $line,
    'message' => 'Недостаточно данных в строке, ожидается 3 значения',
   ];

   echo '[ERROR] Строка ' . ($lineNumber + 1) . ': нужно 3 значения' . "\n";
   logImport($errorData, false, $logFileName);
   continue;
  }

  [$nameRu, $nameEn, $nameDe] = array_map('trim', array_slice($parts, 0, 3));

  $nameRu = normalizeValue($nameRu);
  $nameEn = normalizeValue($nameEn);
  $nameDe = normalizeValue($nameDe);

  if (
   mb_strtolower($nameRu) === 'русский' &&
   mb_strtolower($nameEn) === 'english' &&
   mb_strtolower($nameDe) === 'deutsch'
  ) {
   $stats['skipped']++;
   echo '[SKIPPED HEADER] ' . $line . "\n";
   continue;
  }

  if ($nameRu === '') {
   $stats['errors']++;

   $errorData = [
    'status' => 'row_error',
    'line_number' => $lineNumber + 1,
    'source_line' => $line,
    'message' => 'Пустое русское название города',
   ];

   echo '[ERROR] Строка ' . ($lineNumber + 1) . ': пустое название' . "\n";
   logImport($errorData, false, $logFileName);
   continue;
  }

  $foundIds = [];
  $res = CIBlockElement::GetList(
   [],
   [
    'IBLOCK_ID' => $iblockId,
    '=NAME' => $nameRu,
   ],
   false,
   false,
   ['ID', 'NAME']
  );

  while ($item = $res->Fetch()) {
   $foundIds[] = (int)$item['ID'];
  }

  if (count($foundIds) > 1) {
   $stats['errors']++;

   $errorData = [
    'status' => 'duplicate_name_error',
    'line_number' => $lineNumber + 1,
    'city_name' => $nameRu,
    'found_ids' => $foundIds,
    'message' => 'Найдено несколько элементов с одинаковым NAME, обновление пропущено',
   ];

   echo '[ERROR] Дубликаты NAME для "' . $nameRu . '": ' . implode(', ', $foundIds) . "\n";
   logImport($errorData, false, $logFileName);
   continue;
  }

  $mode = '';
  $elementId = 0;

  if (count($foundIds) === 1) {
   $mode = 'update';
   $elementId = $foundIds[0];

   $updateFields = [
    'NAME' => $nameRu,
    'ACTIVE' => 'Y',
   ];

   $updated = $element->Update($elementId, $updateFields);

   if (!$updated) {
    $stats['errors']++;

    $errorData = [
     'status' => 'update_error',
     'line_number' => $lineNumber + 1,
     'element_id' => $elementId,
     'city_name' => $nameRu,
     'last_error' => $element->LAST_ERROR,
    ];

    echo '[ERROR UPDATE] ' . $nameRu . ' -> ' . $element->LAST_ERROR . "\n";
    logImport($errorData, false, $logFileName);
    continue;
   }

   CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, [
    $propEnId => $nameEn,
    $propDeId => $nameDe,
   ]);

   $stats['updated']++;
  } else {
   $mode = 'add';

   $fields = [
    'IBLOCK_ID' => $iblockId,
    'NAME' => $nameRu,
    'ACTIVE' => 'Y',
    'PROPERTY_VALUES' => [
     $propEnId => $nameEn,
     $propDeId => $nameDe,
    ],
   ];

   $newId = $element->Add($fields);

   if (!$newId) {
    $stats['errors']++;

    $errorData = [
     'status' => 'add_error',
     'line_number' => $lineNumber + 1,
     'city_name' => $nameRu,
     'last_error' => $element->LAST_ERROR,
    ];

    echo '[ERROR ADD] ' . $nameRu . ' -> ' . $element->LAST_ERROR . "\n";
    logImport($errorData, false, $logFileName);
    continue;
   }

   $elementId = (int)$newId;
   $stats['added']++;
  }

  $savedElement = CIBlockElement::GetByID($elementId)->Fetch();

  $savedName = $savedElement ? normalizeValue($savedElement['NAME']) : '';
  $savedEn = getPropertyValueById($iblockId, $elementId, $propEnId);
  $savedDe = getPropertyValueById($iblockId, $elementId, $propDeId);

  $verifyOk =
   ($savedName === $nameRu) &&
   ($savedEn === $nameEn) &&
   ($savedDe === $nameDe);

  if ($verifyOk) {
   $stats['verified_ok']++;
   echo '[' . strtoupper($mode) . ' + VERIFIED] ID=' . $elementId . ' | ' . $nameRu . ' | EN: ' . $nameEn . ' | DE: ' . $nameDe . "\n";
  } else {
   $stats['verified_fail']++;
   echo '[' . strtoupper($mode) . ' + VERIFY_FAIL] ID=' . $elementId . ' | ' . $nameRu . "\n";
  }

  logImport([
   'status' => 'row_processed',
   'line_number' => $lineNumber + 1,
   'mode' => $mode,
   'element_id' => $elementId,
   'input' => [
    'name' => $nameRu,
    'en' => $nameEn,
    'de' => $nameDe,
   ],
   'saved' => [
    'name' => $savedName,
    'en' => $savedEn,
    'de' => $savedDe,
   ],
   'verify_ok' => $verifyOk,
  ], false, $logFileName);

  $stats['processed']++;
 } catch (\Throwable $e) {
  $stats['errors']++;

  echo '[EXCEPTION] Строка ' . ($lineNumber + 1) . ' -> ' . $e->getMessage() . "\n";

  logImport([
   'status' => 'exception',
   'line_number' => $lineNumber + 1,
   'source_line' => $line,
   'message' => $e->getMessage(),
   'file' => $e->getFile(),
   'line_in_file' => $e->getLine(),
   'trace' => $e->getTraceAsString(),
  ], false, $logFileName);
 }
}

logImport([
 'status' => 'finish',
 'message' => 'Импорт завершён',
 'stats' => $stats,
], false, $logFileName);

echo "\n--- ИТОГ ---\n";
print_r($stats);

echo "\nЛог записан в custom logger: " . $logFileName . '_' . date('d.m.Y') . '.log';
echo '</pre>';
