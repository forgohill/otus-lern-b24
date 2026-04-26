<?php

declare(strict_types=1);

namespace Models\Titanic\Install\Iblocks;

use Models\Titanic\Config\TitanicConfig;

class TitanicCabinDecksIblockInstaller extends AbstractDictionaryIblockInstaller
{
  public function getCode(): string
  {
    return TitanicConfig::DECKS_IBLOCK_CODE;
  }

  protected function getName(): string
  {
    return TitanicConfig::DECKS_IBLOCK_NAME;
  }

  protected function getApiCode(): string
  {
    return TitanicConfig::DECKS_IBLOCK_API_CODE;
  }

  protected function getOptionName(): string
  {
    return TitanicConfig::DECKS_IBLOCK_OPTION;
  }

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
