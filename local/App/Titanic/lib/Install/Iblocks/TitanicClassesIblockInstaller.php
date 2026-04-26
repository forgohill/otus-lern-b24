<?php

declare(strict_types=1);

namespace App\Titanic\lib\Install\Iblocks;

use App\Titanic\lib\Config\TitanicConfig;

class TitanicClassesIblockInstaller extends AbstractDictionaryIblockInstaller
{
  public function getCode(): string
  {
    return TitanicConfig::CLASSES_IBLOCK_CODE;
  }

  protected function getName(): string
  {
    return TitanicConfig::CLASSES_IBLOCK_NAME;
  }

  protected function getApiCode(): string
  {
    return TitanicConfig::CLASSES_IBLOCK_API_CODE;
  }

  protected function getOptionName(): string
  {
    return TitanicConfig::CLASSES_IBLOCK_OPTION;
  }

  protected function getElements(): array
  {
    return [
      ['CODE' => 'first', 'NAME' => 'Первый класс'],
      ['CODE' => 'second', 'NAME' => 'Второй класс'],
      ['CODE' => 'third', 'NAME' => 'Третий класс'],
    ];
  }
}
