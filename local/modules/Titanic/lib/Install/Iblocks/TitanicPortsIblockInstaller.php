<?php

declare(strict_types=1);

namespace Models\Titanic\Install\Iblocks;

use Bitrix\Main\Localization\Loc;
use Models\Titanic\Config\TitanicConfig;

Loc::loadMessages(__FILE__);

/**
 * Установщик справочного инфоблока портов посадки.
 */
class TitanicPortsIblockInstaller extends AbstractDictionaryIblockInstaller
{
  /**
   * Возвращает код инфоблока.
   *
   * @return string
   */
  public function getCode(): string
  {
    return TitanicConfig::PORTS_IBLOCK_CODE;
  }

  /**
   * Возвращает название инфоблока.
   *
   * @return string
   */
  protected function getName(): string
  {
    return TitanicConfig::PORTS_IBLOCK_NAME;
  }

  /**
   * Возвращает API-код инфоблока.
   *
   * @return string
   */
  protected function getApiCode(): string
  {
    return TitanicConfig::PORTS_IBLOCK_API_CODE;
  }

  /**
   * Возвращает имя опции, в которой хранится ID инфоблока.
   *
   * @return string
   */
  protected function getOptionName(): string
  {
    return TitanicConfig::PORTS_IBLOCK_OPTION;
  }

  /**
   * Возвращает элементы, которые нужно добавить в инфоблок.
   *
   * @return array<int, array{CODE: string, NAME: string}>
   */
  protected function getElements(): array
  {
    return [
      ['CODE' => 'S', 'NAME' => (string)Loc::getMessage('TITANIC_PORTS_IBLOCK_INSTALLER_ELEMENT_S_NAME')],
      ['CODE' => 'C', 'NAME' => (string)Loc::getMessage('TITANIC_PORTS_IBLOCK_INSTALLER_ELEMENT_C_NAME')],
      ['CODE' => 'Q', 'NAME' => (string)Loc::getMessage('TITANIC_PORTS_IBLOCK_INSTALLER_ELEMENT_Q_NAME')],
      ['CODE' => 'unknown', 'NAME' => (string)Loc::getMessage('TITANIC_PORTS_IBLOCK_INSTALLER_ELEMENT_UNKNOWN_NAME')],
    ];
  }
}
