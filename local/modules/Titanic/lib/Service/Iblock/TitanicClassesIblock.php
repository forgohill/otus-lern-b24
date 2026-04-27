<?php

declare(strict_types=1);

namespace Models\Titanic\Service\Iblock;

use Bitrix\Iblock\Iblock;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Models\Titanic\Config\TitanicConfig;

/**
 * Сервис для работы с инфоблоком `titanic_classes`.
 *
 * Этот класс не является ORM-сущностью.
 * Он нужен, чтобы получить class-string ORM-сущности инфоблока
 * и потом использовать его в `Reference`.
 */
final class TitanicClassesIblock
{
    /**
     * Код инфоблока в системе.
     */
    public static function getCode(): string
    {
        return TitanicConfig::CLASSES_IBLOCK_CODE;
    }

    /**
     * API_CODE инфоблока.
     */
    public static function getApiCode(): string
    {
        return TitanicConfig::CLASSES_IBLOCK_API_CODE;
    }

    /**
     * Название опции, где хранится ID инфоблока.
     */
    public static function getOptionName(): string
    {
        return TitanicConfig::CLASSES_IBLOCK_OPTION;
    }

    /**
     * Возвращает ID инфоблока из опции модуля.
     */
    public static function getIblockId(): ?int
    {
        $iblockId = (int)Option::get(TitanicConfig::MODULE_ID, self::getOptionName(), '0');

        return $iblockId > 0 ? $iblockId : null;
    }

    /**
     * Возвращает class-string ORM-сущности инфоблока.
     *
     * Этот результат можно использовать как цель для ORM Reference.
     *
     * @return class-string
     */
    public static function getEntityDataClass(): string
    {
        self::loadIblockModule();

        $iblockId = self::getIblockId();
        if ($iblockId === null) {
            throw new SystemException('Инфоблок ' . self::getCode() . ' не найден.');
        }

        return Iblock::wakeUp($iblockId)->getEntityDataClass();
    }

    /**
     * Проверяет, что инфоблок уже зарегистрирован в модуле.
     */
    public static function isInstalled(): bool
    {
        return self::getIblockId() !== null;
    }

    private static function loadIblockModule(): void
    {
        if (!Loader::includeModule('iblock')) {
            throw new SystemException('Модуль iblock не подключён.');
        }
    }
}
