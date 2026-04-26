<?php

declare(strict_types=1);

namespace Models\Titanic\Install\Iblocks;

use Models\Titanic\Config\TitanicConfig;

class TitanicPortsIblockInstaller extends AbstractDictionaryIblockInstaller
{
  public function getCode(): string
  {
    return TitanicConfig::PORTS_IBLOCK_CODE;
  }

  protected function getName(): string
  {
    return TitanicConfig::PORTS_IBLOCK_NAME;
  }

  protected function getApiCode(): string
  {
    return TitanicConfig::PORTS_IBLOCK_API_CODE;
  }

  protected function getOptionName(): string
  {
    return TitanicConfig::PORTS_IBLOCK_OPTION;
  }

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
