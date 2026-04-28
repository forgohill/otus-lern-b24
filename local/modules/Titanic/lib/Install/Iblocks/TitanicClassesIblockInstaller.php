<?php

declare(strict_types=1);

namespace Models\Titanic\Install\Iblocks;

use Bitrix\Main\Localization\Loc;
use Models\Titanic\Config\TitanicConfig;

Loc::loadMessages(__FILE__);

/**
 * Установщик справочного инфоблока классов пассажиров.
 */
class TitanicClassesIblockInstaller extends AbstractDictionaryIblockInstaller
{
  /**
   * Возвращает код инфоблока.
   *
   * @return string
   */
  public function getCode(): string
  {
    return TitanicConfig::CLASSES_IBLOCK_CODE;
  }

  /**
   * Возвращает название инфоблока.
   *
   * @return string
   */
  protected function getName(): string
  {
    return TitanicConfig::CLASSES_IBLOCK_NAME;
  }

  /**
   * Возвращает API-код инфоблока.
   *
   * @return string
   */
  protected function getApiCode(): string
  {
    return TitanicConfig::CLASSES_IBLOCK_API_CODE;
  }

  /**
   * Возвращает имя опции, в которой хранится ID инфоблока.
   *
   * @return string
   */
  protected function getOptionName(): string
  {
    return TitanicConfig::CLASSES_IBLOCK_OPTION;
  }

  /**
   * Возвращает элементы, которые нужно добавить в инфоблок.
   *
   * @return array<int, array{CODE: string, NAME: string}>
   */
  protected function getElements(): array
  {
    return [
      ['CODE' => 'first', 'NAME' => (string)Loc::getMessage('TITANIC_CLASSES_IBLOCK_INSTALLER_ELEMENT_FIRST_NAME')],
      ['CODE' => 'second', 'NAME' => (string)Loc::getMessage('TITANIC_CLASSES_IBLOCK_INSTALLER_ELEMENT_SECOND_NAME')],
      ['CODE' => 'third', 'NAME' => (string)Loc::getMessage('TITANIC_CLASSES_IBLOCK_INSTALLER_ELEMENT_THIRD_NAME')],
    ];
  }
}
