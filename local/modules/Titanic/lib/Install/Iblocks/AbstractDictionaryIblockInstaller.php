<?php

declare(strict_types=1);

namespace Models\Titanic\Install\Iblocks;

use Models\Titanic\Config\TitanicConfig;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Config\Option;

/**
 * Базовая установка простого инфоблока-справочника с элементами CODE/NAME.
 */
abstract class AbstractDictionaryIblockInstaller
{
  /**
   * @return array{success: bool, id: int|null, created: bool, elements_created: int, errors: list<string>}
   */
  public function install(): array
  {
    $errors = [];
    $created = false;
    $iblockId = $this->findIblockId();

    if ($iblockId === null) {
      $iblockId = $this->createIblock();
      $created = true;
    }

    if ($iblockId === null) {
      return [
        'success' => false,
        'id' => null,
        'created' => false,
        'elements_created' => 0,
        'errors' => ['Не удалось создать инфоблок ' . $this->getCode()],
      ];
    }

    $iblockValidationErrors = $this->validateIblock($iblockId);

    if ($iblockValidationErrors !== []) {
      return [
        'success' => false,
        'id' => $iblockId,
        'created' => $created,
        'elements_created' => 0,
        'errors' => $iblockValidationErrors,
      ];
    }

    Option::set(TitanicConfig::MODULE_ID, $this->getOptionName(), (string)$iblockId);

    $elementsCreated = $this->ensureElements($iblockId, $errors);

    return [
      'success' => $errors === [],
      'id' => $iblockId,
      'created' => $created,
      'elements_created' => $elementsCreated,
      'errors' => $errors,
    ];
  }

  abstract public function getCode(): string;

  abstract protected function getName(): string;

  abstract protected function getApiCode(): string;

  abstract protected function getOptionName(): string;

  /**
   * @return list<array{CODE: string, NAME: string}>
   */
  abstract protected function getElements(): array;

  public function getTitle(): string
  {
    return $this->getName();
  }

  public function isInstalled(): bool
  {
    $iblockId = $this->findIblockId();

    if ($iblockId === null) {
      return false;
    }

    return $this->validateIblock($iblockId) === [];
  }

  public function getInstallStatus(): string
  {
    if (!$this->isInstalled()) {
      return 'Не установлен';
    }

    return 'Установлен';
  }

  private function findIblockId(): ?int
  {
    $iblock = IblockTable::getRow([
      'select' => ['ID'],
      'filter' => [
        '=IBLOCK_TYPE_ID' => TitanicConfig::IBLOCK_TYPE_ID,
        '=CODE' => $this->getCode(),
      ],
    ]);

    return $iblock === null ? null : (int)$iblock['ID'];
  }

  private function createIblock(): ?int
  {
    $iblock = new \CIBlock();
    $iblockId = (int)$iblock->Add([
      'ACTIVE' => 'Y',
      'NAME' => $this->getName(),
      'CODE' => $this->getCode(),
      'API_CODE' => $this->getApiCode(),
      'IBLOCK_TYPE_ID' => TitanicConfig::IBLOCK_TYPE_ID,
      'SITE_ID' => TitanicConfig::DEFAULT_SITE_IDS,
      'SORT' => 500,
      'VERSION' => 2,
      'GROUP_ID' => [
        2 => 'R',
      ],
    ]);

    return $iblockId > 0 ? $iblockId : null;
  }

  /**
   * @param list<string> $errors
   */
  private function ensureElements(int $iblockId, array &$errors): int
  {
    $created = 0;

    foreach ($this->getElements() as $element) {
      if ($this->findElementId($iblockId, $element['CODE']) !== null) {
        continue;
      }

      $elementId = $this->createElement($iblockId, $element['CODE'], $element['NAME']);

      if ($elementId === null) {
        $errors[] = 'Не удалось создать элемент ' . $element['CODE'] . ' в инфоблоке ' . $this->getCode();
        continue;
      }

      ++$created;
    }

    return $created;
  }

  private function findElementId(int $iblockId, string $code): ?int
  {
    $iterator = \CIBlockElement::GetList(
      [],
      [
        'IBLOCK_ID' => $iblockId,
        '=CODE' => $code,
      ],
      false,
      ['nTopCount' => 1],
      ['ID']
    );

    $element = $iterator->Fetch();

    return is_array($element) ? (int)$element['ID'] : null;
  }

  private function createElement(int $iblockId, string $code, string $name): ?int
  {
    $element = new \CIBlockElement();
    $elementId = (int)$element->Add([
      'IBLOCK_ID' => $iblockId,
      'ACTIVE' => 'Y',
      'CODE' => $code,
      'XML_ID' => $code,
      'NAME' => $name,
    ]);

    return $elementId > 0 ? $elementId : null;
  }

  /**
   * @return list<string>
   */
  private function validateIblock(int $iblockId): array
  {
    $iblock = IblockTable::getRow([
      'select' => ['ID', 'NAME', 'API_CODE', 'IBLOCK_TYPE_ID', 'CODE'],
      'filter' => [
        '=ID' => $iblockId,
      ],
    ]);

    if ($iblock === null) {
      return ['Инфоблок ' . $this->getCode() . ' не найден после определения ID.'];
    }

    $errors = [];

    if ((string)($iblock['IBLOCK_TYPE_ID'] ?? '') !== TitanicConfig::IBLOCK_TYPE_ID) {
      $errors[] = 'Инфоблок ' . $this->getCode() . ' найден в другом типе инфоблоков.';
    }

    if ((string)($iblock['CODE'] ?? '') !== $this->getCode()) {
      $errors[] = 'Инфоблок ' . $this->getCode() . ' найден по другому коду.';
    }

    if ((string)($iblock['NAME'] ?? '') !== $this->getName()) {
      $errors[] = 'У инфоблока ' . $this->getCode() . ' неверное название.';
    }

    if ((string)($iblock['API_CODE'] ?? '') !== $this->getApiCode()) {
      $errors[] = 'У инфоблока ' . $this->getCode() . ' неверный API_CODE.';
    }

    return $errors;
  }
}
