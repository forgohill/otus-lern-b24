<?php

declare(strict_types=1);

namespace Models\Titanic\Install\Iblocks;

use Bitrix\Main\Localization\Loc;
use Models\Titanic\Config\TitanicConfig;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

/**
 * Базовый установщик простого инфоблока-справочника с элементами CODE/NAME.
 *
 * Наследники задают код, название, API_CODE, имя опции и список элементов,
 * которые должны быть добавлены в инфоблок при установке.
 */
abstract class AbstractDictionaryIblockInstaller
{
  /**
   * Устанавливает инфоблок-справочник и его элементы.
   *
   * Если инфоблок уже существует, он не создаётся заново.
   * Если элементы уже есть, они также не создаются повторно.
   *
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
        'errors' => [(string)Loc::getMessage('TITANIC_ABSTRACT_DICTIONARY_IBLOCK_INSTALLER_IBLOCK_CREATE_FAILED', [
          '#CODE#' => $this->getCode(),
        ])],
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

  /**
   * Возвращает код инфоблока.
   *
   * @return non-empty-string
   */
  abstract public function getCode(): string;

  /**
   * Возвращает название инфоблока.
   *
   * @return non-empty-string
   */
  abstract protected function getName(): string;

  /**
   * Возвращает API_CODE инфоблока.
   *
   * @return non-empty-string
   */
  abstract protected function getApiCode(): string;

  /**
   * Возвращает имя опции модуля, в которой хранится ID инфоблока.
   *
   * @return non-empty-string
   */
  abstract protected function getOptionName(): string;

  /**
   * Возвращает список элементов, которые нужно создать в инфоблоке.
   *
   * @return list<array{CODE: string, NAME: string}>
   */
  abstract protected function getElements(): array;

  /**
   * Возвращает название инфоблока для вывода в интерфейсе.
   *
   * @return string
   */
  public function getTitle(): string
  {
    return $this->getName();
  }

  /**
   * Проверяет, установлен ли инфоблок и соответствует ли он ожидаемой конфигурации.
   *
   * @return bool
   */
  public function isInstalled(): bool
  {
    $iblockId = $this->findIblockId();

    if ($iblockId === null) {
      return false;
    }

    return $this->validateIblock($iblockId) === [];
  }

  /**
   * Возвращает строковый статус установки инфоблока.
   *
   * @return string
   */
  public function getInstallStatus(): string
  {
    if (!$this->isInstalled()) {
      return (string)Loc::getMessage('TITANIC_ABSTRACT_DICTIONARY_IBLOCK_INSTALLER_STATUS_NOT_INSTALLED');
    }

    return (string)Loc::getMessage('TITANIC_ABSTRACT_DICTIONARY_IBLOCK_INSTALLER_STATUS_INSTALLED');
  }

  /**
   * Ищет ID инфоблока по типу и коду.
   *
   * @return int|null
   */
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

  /**
   * Создаёт инфоблок в системе.
   *
   * @return int|null
   */
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
   * Создаёт недостающие элементы инфоблока.
   *
   * @param list<string> $errors
   *
   * @return int Количество созданных элементов.
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
        $errors[] = (string)Loc::getMessage('TITANIC_ABSTRACT_DICTIONARY_IBLOCK_INSTALLER_ELEMENT_CREATE_FAILED', [
          '#ELEMENT_CODE#' => $element['CODE'],
          '#CODE#' => $this->getCode(),
        ]);
        continue;
      }

      ++$created;
    }

    return $created;
  }

  /**
   * Ищет элемент инфоблока по коду.
   *
   * @return int|null
   */
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

  /**
   * Создаёт элемент инфоблока.
   *
   * @return int|null
   */
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
   * Проверяет, что найденный инфоблок соответствует ожидаемой конфигурации.
   *
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
      return [
        (string)Loc::getMessage('TITANIC_ABSTRACT_DICTIONARY_IBLOCK_INSTALLER_IBLOCK_NOT_FOUND_AFTER_ID', [
          '#CODE#' => $this->getCode(),
        ]),
      ];
    }

    $errors = [];

    if ((string)($iblock['IBLOCK_TYPE_ID'] ?? '') !== TitanicConfig::IBLOCK_TYPE_ID) {
      $errors[] = (string)Loc::getMessage('TITANIC_ABSTRACT_DICTIONARY_IBLOCK_INSTALLER_IBLOCK_WRONG_TYPE', [
        '#CODE#' => $this->getCode(),
      ]);
    }

    if ((string)($iblock['CODE'] ?? '') !== $this->getCode()) {
      $errors[] = (string)Loc::getMessage('TITANIC_ABSTRACT_DICTIONARY_IBLOCK_INSTALLER_IBLOCK_WRONG_CODE', [
        '#CODE#' => $this->getCode(),
      ]);
    }

    if ((string)($iblock['NAME'] ?? '') !== $this->getName()) {
      $errors[] = (string)Loc::getMessage('TITANIC_ABSTRACT_DICTIONARY_IBLOCK_INSTALLER_IBLOCK_WRONG_NAME', [
        '#CODE#' => $this->getCode(),
      ]);
    }

    if ((string)($iblock['API_CODE'] ?? '') !== $this->getApiCode()) {
      $errors[] = (string)Loc::getMessage('TITANIC_ABSTRACT_DICTIONARY_IBLOCK_INSTALLER_IBLOCK_WRONG_API_CODE', [
        '#CODE#' => $this->getCode(),
      ]);
    }

    return $errors;
  }
}
