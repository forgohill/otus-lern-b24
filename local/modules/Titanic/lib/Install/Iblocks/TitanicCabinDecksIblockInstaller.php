<?php

declare(strict_types=1);

namespace Models\Titanic\Install\Iblocks;

use Models\Titanic\Config\TitanicConfig;

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
      ['CODE' => 'A', 'NAME' => 'Палуба A'],
      ['CODE' => 'B', 'NAME' => 'Палуба B'],
      ['CODE' => 'C', 'NAME' => 'Палуба C'],
      ['CODE' => 'D', 'NAME' => 'Палуба D'],
      ['CODE' => 'E', 'NAME' => 'Палуба E'],
      ['CODE' => 'F', 'NAME' => 'Палуба F'],
      ['CODE' => 'G', 'NAME' => 'Палуба G'],
      ['CODE' => 'T', 'NAME' => 'Палуба T'],
      ['CODE' => 'unknown', 'NAME' => 'Палуба неизвестна'],
    ];
  }
}
