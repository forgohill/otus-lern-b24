<?php

declare(strict_types=1);

namespace Models\Titanic\Service\Iblock;

use Bitrix\Iblock\Iblock;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Models\Titanic\Config\TitanicConfig;

/**
 * Service for working with the `titanic_ports` iblock.
 *
 * This class is not an ORM entity itself.
 * It resolves the ORM entity class of the iblock,
 * so it can be used later in `Reference`.
 */
final class TitanicPortsIblock
{
    public static function getCode(): string
    {
        return TitanicConfig::PORTS_IBLOCK_CODE;
    }

    public static function getApiCode(): string
    {
        return TitanicConfig::PORTS_IBLOCK_API_CODE;
    }

    public static function getOptionName(): string
    {
        return TitanicConfig::PORTS_IBLOCK_OPTION;
    }

    public static function getIblockId(): ?int
    {
        $iblockId = (int)Option::get(TitanicConfig::MODULE_ID, self::getOptionName(), '0');

        return $iblockId > 0 ? $iblockId : null;
    }

    /**
     * Returns the ORM entity class-string of the iblock.
     *
     * @return class-string
     */
    public static function getEntityDataClass(): string
    {
        self::loadIblockModule();

        $iblockId = self::getIblockId();
        if ($iblockId === null) {
            throw new SystemException('Iblock ' . self::getCode() . ' is not found.');
        }

        return Iblock::wakeUp($iblockId)->getEntityDataClass();
    }

    public static function isInstalled(): bool
    {
        return self::getIblockId() !== null;
    }

    private static function loadIblockModule(): void
    {
        if (!Loader::includeModule('iblock')) {
            throw new SystemException('Module iblock is not loaded.');
        }
    }
}
