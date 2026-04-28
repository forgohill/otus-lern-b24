<?php

declare(strict_types=1);

namespace Models\Titanic\Install\Iblocks;

use Bitrix\Main\Localization\Loc;
use Models\Titanic\Config\TitanicConfig;

Loc::loadMessages(__FILE__);

/**
 * Установщик справочного инфоблока палуб кают.
 */
class TitanicCabinDecksIblockInstaller extends AbstractDictionaryIblockInstaller
{
  /**
   * Возвращает код инфоблока.
   *
   * @return string
   */
  public function getCode(): string
  {
    return TitanicConfig::DECKS_IBLOCK_CODE;
  }

  /**
   * Возвращает название инфоблока.
   *
   * @return string
   */
  protected function getName(): string
  {
    return TitanicConfig::DECKS_IBLOCK_NAME;
  }

  /**
   * Возвращает API-код инфоблока.
   *
   * @return string
   */
  protected function getApiCode(): string
  {
    return TitanicConfig::DECKS_IBLOCK_API_CODE;
  }

  /**
   * Возвращает имя опции, в которой хранится ID инфоблока.
   *
   * @return string
   */
  protected function getOptionName(): string
  {
    return TitanicConfig::DECKS_IBLOCK_OPTION;
  }

  /**
   * Возвращает элементы, которые нужно добавить в инфоблок.
   *
   * @return array<int, array{CODE: string, NAME: string}>
   */
  protected function getElements(): array
  {
    return [
      ['CODE' => 'A', 'NAME' => (string)Loc::getMessage('TITANIC_CABIN_DECKS_IBLOCK_INSTALLER_ELEMENT_A_NAME')],
      ['CODE' => 'B', 'NAME' => (string)Loc::getMessage('TITANIC_CABIN_DECKS_IBLOCK_INSTALLER_ELEMENT_B_NAME')],
      ['CODE' => 'C', 'NAME' => (string)Loc::getMessage('TITANIC_CABIN_DECKS_IBLOCK_INSTALLER_ELEMENT_C_NAME')],
      ['CODE' => 'D', 'NAME' => (string)Loc::getMessage('TITANIC_CABIN_DECKS_IBLOCK_INSTALLER_ELEMENT_D_NAME')],
      ['CODE' => 'E', 'NAME' => (string)Loc::getMessage('TITANIC_CABIN_DECKS_IBLOCK_INSTALLER_ELEMENT_E_NAME')],
      ['CODE' => 'F', 'NAME' => (string)Loc::getMessage('TITANIC_CABIN_DECKS_IBLOCK_INSTALLER_ELEMENT_F_NAME')],
      ['CODE' => 'G', 'NAME' => (string)Loc::getMessage('TITANIC_CABIN_DECKS_IBLOCK_INSTALLER_ELEMENT_G_NAME')],
      ['CODE' => 'T', 'NAME' => (string)Loc::getMessage('TITANIC_CABIN_DECKS_IBLOCK_INSTALLER_ELEMENT_T_NAME')],
      ['CODE' => 'unknown', 'NAME' => (string)Loc::getMessage('TITANIC_CABIN_DECKS_IBLOCK_INSTALLER_ELEMENT_UNKNOWN_NAME')],
    ];
  }
}
