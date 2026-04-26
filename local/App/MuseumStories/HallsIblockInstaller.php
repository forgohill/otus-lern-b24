<?php

declare(strict_types=1);

namespace App\MuseumStories;

use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\TypeTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

/**
 * Создаёт инфоблок музейных залов и его базовые свойства.
 *
 * В Bitrix структура инфоблока создаётся через CIBlock::Add, а свойства
 * отдельно через CIBlockProperty::Add.
 */
class HallsIblockInstaller
{
  /**
   * Создаёт инфоблок и необходимые свойства.
   *
   * Метод идемпотентный: если инфоблок уже существует, он только проверит и
   * добавит отсутствующие свойства.
   *
   * @param list<string> $siteIds
   *
   * @return array{
   *   success: bool,
   *   id: int|null,
   *   errors: list<string>
   * }
   */
  public function install(array $siteIds = []): array
  {
    $this->loadIblockModule();

    $siteIds = $this->normalizeSiteIds($siteIds);
    $iblockTypeId = MuseumStoriesConfig::IBLOCK_TYPE_ID;

    $typeResult = $this->ensureIblockType($iblockTypeId);

    if (!$typeResult['success']) {
      return [
        'success' => false,
        'id' => null,
        'errors' => $typeResult['errors'],
      ];
    }

    $buildingIblock = $this->findExistingIblock(MuseumStoriesConfig::IBLOCK_CODE);

    if ($buildingIblock === null || (string)$buildingIblock['IBLOCK_TYPE_ID'] !== $iblockTypeId) {
      return [
        'success' => false,
        'id' => null,
        'errors' => [
          Loc::getMessage('APP_MUSEUM_STORIES_HALLS_BUILDINGS_IBLOCK_REQUIRED'),
        ],
      ];
    }

    $existingIblock = $this->findExistingIblock(MuseumStoriesConfig::HALLS_IBLOCK_CODE);

    if ($existingIblock !== null) {
      return [
        'success' => false,
        'id' => (int)$existingIblock['ID'],
        'errors' => [
          Loc::getMessage('APP_MUSEUM_STORIES_IBLOCK_ALREADY_CREATED'),
        ],
      ];
    }

    $iblockId = (int)($existingIblock['ID'] ?? 0);

    global $DB;

    $DB->StartTransaction();

    try {
      if ($iblockId <= 0) {
        $iblock = new \CIBlock();
        $iblockId = (int)$iblock->Add($this->buildIblockFields($siteIds));

        if ($iblockId <= 0) {
          $DB->Rollback();

          return [
            'success' => false,
            'id' => null,
            'errors' => [
              Loc::getMessage('APP_MUSEUM_STORIES_HALLS_IBLOCK_CREATE_ERROR'),
              trim((string)$iblock->LAST_ERROR),
            ],
          ];
        }
      }

      $propertyErrors = $this->ensureProperties($iblockId, (int)$buildingIblock['ID']);

      if ($propertyErrors !== []) {
        $DB->Rollback();

        return [
          'success' => false,
          'id' => null,
          'errors' => $propertyErrors,
        ];
      }

      $DB->Commit();

      return [
        'success' => true,
        'id' => $iblockId,
        'errors' => [],
      ];
    } catch (\Throwable $exception) {
      $DB->Rollback();

      return [
        'success' => false,
        'id' => null,
        'errors' => [
          $exception->getMessage(),
        ],
      ];
    }
  }

  /**
   * Формирует поля инфоблока.
   *
   * @param list<string> $siteIds
   *
   * @return array<string, mixed>
   */
  private function buildIblockFields(array $siteIds): array
  {
    return [
      'ACTIVE' => 'Y',
      'NAME' => MuseumStoriesConfig::HALLS_IBLOCK_NAME,
      'CODE' => MuseumStoriesConfig::HALLS_IBLOCK_CODE,
      'API_CODE' => MuseumStoriesConfig::HALLS_IBLOCK_API_CODE,
      'IBLOCK_TYPE_ID' => MuseumStoriesConfig::IBLOCK_TYPE_ID,
      'SITE_ID' => $siteIds,
      'SORT' => 500,
      'VERSION' => 2,
      'DESCRIPTION' => MuseumStoriesConfig::HALLS_IBLOCK_DESCRIPTION,
      'DESCRIPTION_TYPE' => 'text',
    ];
  }

  /**
   * Проверяет и создаёт отсутствующие свойства инфоблока.
   *
   * @return list<string>
   */
  private function ensureProperties(int $iblockId, int $buildingIblockId): array
  {
    $errors = [];

    foreach ($this->getPropertyDefinitions($buildingIblockId) as $propertyFields) {
      if ($this->propertyExists($iblockId, (string)$propertyFields['CODE'])) {
        continue;
      }

      $property = new \CIBlockProperty();
      $propertyId = (int)$property->Add($propertyFields + ['IBLOCK_ID' => $iblockId]);

      if ($propertyId <= 0) {
        $errors[] = Loc::getMessage('APP_MUSEUM_STORIES_HALLS_IBLOCK_PROPERTY_CREATE_ERROR');
        $lastError = trim((string)$property->LAST_ERROR);

        if ($lastError !== '') {
          $errors[] = $lastError;
        }
      }
    }

    return array_values(array_unique(array_filter($errors)));
  }

  /**
   * Возвращает список свойств для инфоблока залов.
   *
   * @return list<array<string, mixed>>
   */
  private function getPropertyDefinitions(int $buildingIblockId): array
  {
    return [
      [
        'NAME' => 'Источник',
        'CODE' => MuseumStoriesConfig::PROPERTY_SOURCE_ID,
        'ACTIVE' => 'Y',
        'SORT' => 100,
        'PROPERTY_TYPE' => 'N',
      ],
      [
        'NAME' => 'Привязка к зданию',
        'CODE' => MuseumStoriesConfig::HALL_BUILDING_REF,
        'ACTIVE' => 'Y',
        'SORT' => 200,
        'PROPERTY_TYPE' => 'E',
        'LINK_IBLOCK_ID' => $buildingIblockId,
      ],
      [
        'NAME' => 'Источник здания',
        'CODE' => MuseumStoriesConfig::HALL_BUILDING_SOURCE_ID,
        'ACTIVE' => 'Y',
        'SORT' => 300,
        'PROPERTY_TYPE' => 'N',
      ],
      [
        'NAME' => 'Источник этажа',
        'CODE' => MuseumStoriesConfig::HALL_FLOOR_SOURCE_ID,
        'ACTIVE' => 'Y',
        'SORT' => 400,
        'PROPERTY_TYPE' => 'N',
      ],
      [
        'NAME' => 'Номер этажа',
        'CODE' => MuseumStoriesConfig::HALL_FLOOR_NUMBER,
        'ACTIVE' => 'Y',
        'SORT' => 500,
        'PROPERTY_TYPE' => 'N',
      ],
      [
        'NAME' => 'Номер зала',
        'CODE' => MuseumStoriesConfig::HALL_HALL_NUMBER,
        'ACTIVE' => 'Y',
        'SORT' => 600,
        'PROPERTY_TYPE' => 'N',
      ],
      [
        'NAME' => 'План',
        'CODE' => MuseumStoriesConfig::HALL_PLAN_PATH,
        'ACTIVE' => 'Y',
        'SORT' => 700,
        'PROPERTY_TYPE' => 'S',
      ],
      [
        'NAME' => 'Изображение',
        'CODE' => MuseumStoriesConfig::HALL_IMAGE_PATH,
        'ACTIVE' => 'Y',
        'SORT' => 800,
        'PROPERTY_TYPE' => 'S',
      ],
      [
        'NAME' => 'Виртуальный тур',
        'CODE' => MuseumStoriesConfig::HALL_VIRTUAL_TOUR_URL,
        'ACTIVE' => 'Y',
        'SORT' => 900,
        'PROPERTY_TYPE' => 'S',
      ],
    ];
  }

  /**
   * Ищет инфоблок по коду.
   *
   * @return array<string, mixed>|null
   */
  private function findExistingIblock(string $code): ?array
  {
    return IblockTable::getRow([
      'select' => ['ID', 'IBLOCK_TYPE_ID'],
      'filter' => [
        '=CODE' => $code,
      ],
    ]);
  }

  /**
   * Проверяет наличие типа инфоблока.
   */
  private function iblockTypeExists(string $iblockTypeId): bool
  {
    return (bool)TypeTable::getRow([
      'select' => ['ID'],
      'filter' => ['=ID' => $iblockTypeId],
    ]);
  }

  /**
   * Создаёт тип инфоблока, если его ещё нет.
   *
   * @return array{success: bool, errors: list<string>}
   */
  private function ensureIblockType(string $iblockTypeId): array
  {
    if ($this->iblockTypeExists($iblockTypeId)) {
      return [
        'success' => true,
        'errors' => [],
      ];
    }

    $iblockType = new \CIBlockType();
    $result = $iblockType->Add($this->buildIblockTypeFields($iblockTypeId));

    if ($result === false) {
      $errors = [Loc::getMessage('APP_MUSEUM_STORIES_IBLOCK_TYPE_CREATE_ERROR')];
      $lastError = trim((string)$iblockType->LAST_ERROR);

      if ($lastError !== '') {
        $errors[] = $lastError;
      }

      return [
        'success' => false,
        'errors' => array_values(array_filter($errors)),
      ];
    }

    return [
      'success' => true,
      'errors' => [],
    ];
  }

  /**
   * Формирует поля для создания типа инфоблоков.
   *
   * @return array<string, mixed>
   */
  private function buildIblockTypeFields(string $iblockTypeId): array
  {
    $langId = defined('LANGUAGE_ID') ? LANGUAGE_ID : 'ru';

    return [
      'ID' => $iblockTypeId,
      'SECTIONS' => 'Y',
      'IN_RSS' => 'N',
      'SORT' => 500,
      'LANG' => [
        $langId => [
          'NAME' => 'Lists',
          'SECTION_NAME' => 'Sections',
          'ELEMENT_NAME' => 'Items',
        ],
      ],
    ];
  }

  /**
   * Проверяет, существует ли уже свойство с указанным кодом.
   */
  private function propertyExists(int $iblockId, string $propertyCode): bool
  {
    return (bool)PropertyTable::getRow([
      'select' => ['ID'],
      'filter' => [
        '=IBLOCK_ID' => $iblockId,
        '=CODE' => $propertyCode,
      ],
    ]);
  }

  /**
   * Подключает модуль инфоблоков перед работой с API.
   */
  private function loadIblockModule(): void
  {
    if (!Loader::includeModule('iblock')) {
      throw new SystemException(
        Loc::getMessage('APP_MUSEUM_STORIES_IBLOCK_MODULE_REQUIRED')
      );
    }
  }

  /**
   * Нормализует список сайтов для привязки инфоблока.
   *
   * @param list<string> $siteIds
   *
   * @return list<string>
   */
  private function normalizeSiteIds(array $siteIds): array
  {
    $siteIds = array_values(array_unique(array_filter(array_map(
      static fn(mixed $siteId): string => trim((string)$siteId),
      $siteIds
    ))));

    if ($siteIds === []) {
      return MuseumStoriesConfig::DEFAULT_SITE_IDS;
    }

    return $siteIds;
  }
}
