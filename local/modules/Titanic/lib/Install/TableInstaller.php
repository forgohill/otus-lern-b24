<?php

declare(strict_types=1);

namespace Models\Titanic\Install;

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Entity as Base;
use Models\Titanic\Orm\CabinsTable;
use Models\Titanic\Orm\PassengerCabinTable;
use Models\Titanic\Orm\PassengersTable;
use Models\Titanic\Orm\TicketsTable;

Loc::loadMessages(__FILE__);

/**
 * Устанавливает таблицы базы данных Titanic по определениям ORM-классов D7.
 *
 * Использует `DataManager`-классы как источник схемы таблиц и создаёт
 * или удаляет таблицы через соединение БД Bitrix.
 */
class TableInstaller
{
    /**
     * Устанавливает все таблицы модуля.
     *
     * Возвращает общий результат и подробности по каждой таблице.
     *
     * @return array{
     *   success: bool,
     *   tables: array<string, array{success: bool, table: string|null, created: bool, errors: list<string>}>,
     *   errors: list<string>
     * }
     */
    public function install(): array
    {
        $errors = [];
        $tables = [];

        foreach ($this->getTableClasses() as $tableClass) {
            $result = $this->installTable($tableClass);
            $tables[$tableClass] = $result;

            if (!$result['success']) {
                $errors = array_merge($errors, $result['errors']);
            }
        }

        return [
            'success' => $errors === [],
            'tables' => $tables,
            'errors' => $errors,
        ];
    }

    /**
     * Удаляет все таблицы модуля.
     *
     * Возвращает общий результат и подробности по каждой таблице.
     *
     * @return array{
     *   success: bool,
     *   tables: array<string, array{success: bool, table: string|null, dropped: bool, errors: list<string>}>,
     *   errors: list<string>
     * }
     */
    public function uninstall(): array
    {
        $errors = [];
        $tables = [];

        foreach ($this->getUninstallTableClasses() as $tableClass) {
            $result = $this->uninstallTable($tableClass);
            $tables[$tableClass] = $result;

            if (!$result['success']) {
                $errors = array_merge($errors, $result['errors']);
            }
        }

        return [
            'success' => $errors === [],
            'tables' => $tables,
            'errors' => $errors,
        ];
    }

    /**
     * Возвращает текущее состояние таблиц модуля.
     *
     * @return array<string, array{table: string, installed: bool}>
     */
    public function getTableStates(): array
    {
        $states = [];

        foreach ($this->getTableClasses() as $tableClass) {
            $tableName = $tableClass::getTableName();
            $connection = Application::getConnection($tableClass::getConnectionName());

            $states[$tableClass] = [
                'table' => $tableName,
                'installed' => $connection->isTableExists($tableName),
            ];
        }

        return $states;
    }

    /**
     * Проверяет, установлены ли все таблицы модуля.
     *
     * @return bool
     */
    public function isInstalled(): bool
    {
        foreach ($this->getTableStates() as $state) {
            if (!$state['installed']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Проверяет, существуют ли ещё таблицы модуля в базе данных.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        foreach ($this->getTableStates() as $state) {
            if ($state['installed']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Возвращает список ORM-классов таблиц модуля в порядке установки.
     *
     * @return list<class-string<DataManager>>
     */
    public function getTableClasses(): array
    {
        return [
            TicketsTable::class,
            CabinsTable::class,
            PassengersTable::class,
            PassengerCabinTable::class,
        ];
    }

    /**
     * Возвращает список ORM-классов таблиц в обратном порядке для удаления.
     *
     * @return list<class-string<DataManager>>
     */
    public function getUninstallTableClasses(): array
    {
        return [
            PassengerCabinTable::class,
            PassengersTable::class,
            CabinsTable::class,
            TicketsTable::class,
        ];
    }

    /**
     * Создаёт таблицу по ORM-классу, если её ещё нет.
     *
     * @param class-string<DataManager> $tableClass
     *
     * @return array{success: bool, table: string|null, created: bool, errors: list<string>}
     */
    private function installTable(string $tableClass): array
    {
        try {
            $tableName = $tableClass::getTableName();
            $connection = Application::getConnection($tableClass::getConnectionName());

            if ($connection->isTableExists($tableName)) {
                return [
                    'success' => true,
                    'table' => $tableName,
                    'created' => false,
                    'errors' => [],
                ];
            }

            $entity = Base::getInstance($tableClass);
            $entity->createDbTable();

            return [
                'success' => true,
                'table' => $tableName,
                'created' => true,
                'errors' => [],
            ];
        } catch (\Throwable $exception) {
            return [
                'success' => false,
                'table' => null,
                'created' => false,
                'errors' => [
                    $this->formatCreateError($tableClass, $exception),
                ],
            ];
        }
    }

    /**
     * Удаляет таблицу по ORM-классу, если она существует.
     *
     * @param class-string<DataManager> $tableClass
     *
     * @return array{success: bool, table: string|null, dropped: bool, errors: list<string>}
     */
    private function uninstallTable(string $tableClass): array
    {
        try {
            $tableName = $tableClass::getTableName();
            $connection = Application::getConnection($tableClass::getConnectionName());

            if (!$connection->isTableExists($tableName)) {
                return [
                    'success' => true,
                    'table' => $tableName,
                    'dropped' => false,
                    'errors' => [],
                ];
            }

            $connection->dropTable($tableName);

            return [
                'success' => true,
                'table' => $tableName,
                'dropped' => true,
                'errors' => [],
            ];
        } catch (\Throwable $exception) {
            return [
                'success' => false,
                'table' => null,
                'dropped' => false,
                'errors' => [
                    $this->formatDropError($tableClass, $exception),
                ],
            ];
        }
    }

    private function formatCreateError(string $tableClass, \Throwable $exception): string
    {
        return (string)Loc::getMessage(
            'TITANIC_TABLE_INSTALLER_TABLE_CREATE_FAILED',
            [
                '#TABLE_CLASS#' => $tableClass,
                '#ERROR#' => $exception->getMessage(),
            ]
        );
    }

    private function formatDropError(string $tableClass, \Throwable $exception): string
    {
        return (string)Loc::getMessage(
            'TITANIC_TABLE_INSTALLER_TABLE_DROP_FAILED',
            [
                '#TABLE_CLASS#' => $tableClass,
                '#ERROR#' => $exception->getMessage(),
            ]
        );
    }
}
