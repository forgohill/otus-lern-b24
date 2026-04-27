<?php

declare(strict_types=1);

namespace Models\Titanic\Install;

use Bitrix\Main\Application;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Entity as Base;
use Models\Titanic\Orm\CabinsTable;
use Models\Titanic\Orm\PassengerCabinTable;
use Models\Titanic\Orm\PassengersTable;
use Models\Titanic\Orm\TicketsTable;

/**
 * Installs Titanic database tables from D7 ORM DataManager definitions.
 */
class TableInstaller
{
    /**
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

    public function isInstalled(): bool
    {
        foreach ($this->getTableStates() as $state) {
            if (!$state['installed']) {
                return false;
            }
        }

        return true;
    }

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
                    sprintf(
                        'Не удалось создать таблицу для %s: %s',
                        $tableClass,
                        $exception->getMessage()
                    ),
                ],
            ];
        }
    }

    /**
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
                    sprintf(
                        'Не удалось удалить таблицу для %s: %s',
                        $tableClass,
                        $exception->getMessage()
                    ),
                ],
            ];
        }
    }
}
