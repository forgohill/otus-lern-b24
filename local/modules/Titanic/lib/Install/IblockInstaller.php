<?php

declare(strict_types=1);

namespace Models\Titanic\Install;

use Models\Titanic\Config\TitanicConfig;
use Models\Titanic\Install\Iblocks\TitanicCabinDecksIblockInstaller;
use Models\Titanic\Install\Iblocks\TitanicClassesIblockInstaller;
use Models\Titanic\Install\Iblocks\TitanicPortsIblockInstaller;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

/**
 * Обработчик установки справочных инфоблоков проекта Titanic.
 *
 * Собирает установщики отдельных инфоблоков, подключает модуль `iblock`
 * и возвращает общий результат установки.
 */
class IblockInstaller
{
  /**
   * Устанавливает все справочные инфоблоки проекта.
   *
   * Возвращает общий результат установки и результаты по каждому инфоблоку.
   *
   * @return array{
   *   success: bool,
   *   iblocks: array<string, array<string, mixed>>,
   *   errors: list<string>
   * }
   *
   * @throws SystemException Если модуль `iblock` не удалось подключить.
   */
  public function install(): array
  {
    $this->loadIblockModule();

    $errors = [];
    $iblocks = [];

    foreach ($this->getDictionaryInstallers() as $installer) {
      $result = $installer->install();
      $iblocks[$installer->getCode()] = $result;

      if (!$result['success']) {
        $errors = array_merge($errors, $result['errors']);
      }
    }

    return [
      'success' => $errors === [],
      'iblocks' => $iblocks,
      'errors' => $errors,
    ];
  }

  /**
   * Возвращает список установщиков справочных инфоблоков.
   *
   * @return list<Iblocks\AbstractDictionaryIblockInstaller>
   */
  public function getDictionaryInstallers(): array
  {
    return [
      new TitanicClassesIblockInstaller(),
      new TitanicPortsIblockInstaller(),
      new TitanicCabinDecksIblockInstaller(),
    ];
  }

  /**
   * Подключает модуль `iblock` перед выполнением установки.
   *
   * @throws SystemException Если модуль `iblock` не подключён.
   */
  private function loadIblockModule(): void
  {
    if (!Loader::includeModule('iblock')) {
      throw new SystemException('Модуль iblock не подключён.');
    }
  }
}
