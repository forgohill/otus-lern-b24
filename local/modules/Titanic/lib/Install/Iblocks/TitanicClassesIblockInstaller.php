<?php

declare(strict_types=1);

namespace Models\Titanic\Install\Iblocks;

use Models\Titanic\Config\TitanicConfig;

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
      ['CODE' => 'first', 'NAME' => 'Первый класс'],
      ['CODE' => 'second', 'NAME' => 'Второй класс'],
      ['CODE' => 'third', 'NAME' => 'Третий класс'],
    ];
  }
}
