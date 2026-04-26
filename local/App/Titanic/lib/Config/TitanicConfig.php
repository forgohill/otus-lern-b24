<?php

declare(strict_types=1);

namespace App\Titanic\lib\Config;

/**
 * Общие коды учебного проекта Titanic.
 */
final class TitanicConfig
{
  public const MODULE_ID = 'otus.titanic';
  public const IBLOCK_TYPE_ID = 'lists';
  public const DEFAULT_SITE_IDS = ['s1'];

  public const CLASSES_IBLOCK_CODE = 'titanic_classes';
  public const CLASSES_IBLOCK_NAME = 'Классы Titanic';
  public const CLASSES_IBLOCK_API_CODE = 'TitanicClasses';
  public const CLASSES_IBLOCK_OPTION = 'IBLOCK_CLASSES_ID';

  public const PORTS_IBLOCK_CODE = 'titanic_ports';
  public const PORTS_IBLOCK_NAME = 'Порты посадки Titanic';
  public const PORTS_IBLOCK_API_CODE = 'TitanicPorts';
  public const PORTS_IBLOCK_OPTION = 'IBLOCK_PORTS_ID';

  public const DECKS_IBLOCK_CODE = 'titanic_cabin_decks';
  public const DECKS_IBLOCK_NAME = 'Палубы Titanic';
  public const DECKS_IBLOCK_API_CODE = 'TitanicCabinDecks';
  public const DECKS_IBLOCK_OPTION = 'IBLOCK_DECKS_ID';

  private function __construct()
  {
  }
}
