<?php

declare(strict_types=1);

namespace Models\Titanic\Install\Iblocks;

use Models\Titanic\Config\TitanicConfig;

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
      ['CODE' => 'S', 'NAME' => 'Southampton'],
      ['CODE' => 'C', 'NAME' => 'Cherbourg'],
      ['CODE' => 'Q', 'NAME' => 'Queenstown'],
      ['CODE' => 'unknown', 'NAME' => 'Неизвестно'],
    ];
  }
}
