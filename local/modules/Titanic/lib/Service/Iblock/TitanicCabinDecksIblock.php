<?php

declare(strict_types=1);

namespace Models\Titanic\Service\Iblock;

use Bitrix\Iblock\Iblock;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Models\Titanic\Config\TitanicConfig;

Loc::loadMessages(__FILE__);

/**
 * Сервис для работы с инфоблоком `titanic_cabin_decks`.
 *
 * Сам класс не является ORM-сущностью.
 * Он получает класс ORM-сущности инфоблока,
 * чтобы потом его можно было использовать в `Reference`.
 *
 * @internal Лёгкий вспомогательный класс для доступа к настройкам инфоблока и его ORM-сущности.
 */
final class TitanicCabinDecksIblock
{
    /**
     * Возвращает код инфоблока, используемый в конфигурации Bitrix.
     *
     * @return non-empty-string
     */
    public static function getCode(): string
    {
        return TitanicConfig::DECKS_IBLOCK_CODE;
    }

    /**
     * Возвращает API-код инфоблока.
     *
     * @return non-empty-string
     */
    public static function getApiCode(): string
    {
        return TitanicConfig::DECKS_IBLOCK_API_CODE;
    }

    /**
     * Возвращает имя опции модуля, в которой хранится ID инфоблока.
     *
     * @return non-empty-string
     */
    public static function getOptionName(): string
    {
        return TitanicConfig::DECKS_IBLOCK_OPTION;
    }

    /**
     * Возвращает сохранённый ID инфоблока или `null`, если инфоблок не установлен.
     *
     * @return int|null
     */
    public static function getIblockId(): ?int
    {
        $iblockId = (int)Option::get(TitanicConfig::MODULE_ID, self::getOptionName(), '0');

        return $iblockId > 0 ? $iblockId : null;
    }

    /**
     * Возвращает class-string ORM-сущности для инфоблока.
     *
     * @return class-string
     *
     * @throws SystemException Если модуль `iblock` не загружен или инфоблок не найден.
     */
    public static function getEntityDataClass(): string
    {
        self::loadIblockModule();

        $iblockId = self::getIblockId();
        if ($iblockId === null) {
            throw new SystemException(
                (string)Loc::getMessage(
                    'TITANIC_CABIN_DECKS_IBLOCK_NOT_FOUND',
                    ['#CODE#' => self::getCode()]
                )
            );
        }

        return Iblock::wakeUp($iblockId)->getEntityDataClass();
    }

    /**
     * Проверяет, установлен ли инфоблок.
     *
     * @return bool
     */
    public static function isInstalled(): bool
    {
        return self::getIblockId() !== null;
    }

    /**
     * Загружает модуль `iblock` перед использованием API инфоблоков Bitrix.
     *
     * @throws SystemException Если модуль не удалось подключить.
     */
    private static function loadIblockModule(): void
    {
        if (!Loader::includeModule('iblock')) {
            throw new SystemException((string)Loc::getMessage('TITANIC_CABIN_DECKS_IBLOCK_MODULE_NOT_LOADED'));
        }
    }
}
