<?php

declare(strict_types=1);

namespace Models\Titanic\Service;

use Bitrix\Main\Application;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\ORM\Data\AddResult;
use Models\Titanic\Orm\CabinsTable;
use Models\Titanic\Orm\PassengerCabinTable;
use Models\Titanic\Orm\PassengersTable;
use Models\Titanic\Orm\TicketsTable;
use Models\Titanic\Service\Iblock\TitanicClassesIblock;
use Models\Titanic\Service\Iblock\TitanicPortsIblock;
use RuntimeException;

/**
 * Импортирует пассажиров из Titanic CSV в таблицу `otus_titanic_passengers`.
 *
 * Сервис также связывает пассажира с:
 * - билетом;
 * - классом пассажира;
 * - портом посадки;
 * - каютами через таблицу `otus_titanic_passenger_cabin`.
 */
final class TitanicPassengersImporter
{
    private TitanicCsvParser $parser;

    private TitanicCabinParser $cabinParser;

    private TitanicTicketsImporter $ticketsImporter;

    public function __construct(
        ?TitanicCsvParser $parser = null,
        ?TitanicCabinParser $cabinParser = null,
        ?TitanicTicketsImporter $ticketsImporter = null
    )
    {
        $this->parser = $parser ?? new TitanicCsvParser();
        $this->cabinParser = $cabinParser ?? new TitanicCabinParser();
        $this->ticketsImporter = $ticketsImporter ?? new TitanicTicketsImporter();
    }

    /**
     * Читает CSV, готовит пассажиров и сохраняет их в ORM-таблицу.
     *
     * @return array{
     *   success: bool,
     *   total_rows: int,
     *   unique_passengers: int,
     *   created: int,
     *   skipped: int,
     *   errors: list<string>
     * }
     */
    public function import(string $csvPath): array
    {
        $errors = [];
        $created = 0;
        $skipped = 0;

        $rows = [];
        $passengers = [];

        $connection = Application::getConnection(PassengersTable::getConnectionName());

        try {
            $rows = $this->parser->parse($csvPath);
            $passengers = $this->collectUniquePassengersFromRows($rows);

            $ticketMap = $this->loadTicketMap();
            $classMap = $this->loadClassMap();
            $portMap = $this->loadPortMap();
            $cabinMap = $this->loadCabinMap($this->collectCabinCodesFromPassengers($passengers));
            $existingPassengers = $this->loadExistingPassengers(array_column($passengers, 'PASSENGER_EXTERNAL_ID'));

            $connection->startTransaction();

            foreach ($passengers as $passengerData) {
                $passengerExternalId = (int)$passengerData['PASSENGER_EXTERNAL_ID'];

                if (isset($existingPassengers[(string)$passengerExternalId])) {
                    throw new RuntimeException(sprintf(
                        'Пассажир с внешним ID %d уже существует в таблице.',
                        $passengerExternalId
                    ));
                }

                $ticketId = $this->resolveTicketId((string)$passengerData['TICKET_KEY'], $ticketMap);
                $classElementId = $this->resolveClassElementId((int)$passengerData['PCLASS_VALUE'], $classMap);
                $embarkedElementId = $this->resolvePortElementId($passengerData['EMBARKED_CODE'], $portMap);
                $cabinDeckElementId = $this->resolveCabinDeckElementId($passengerData['CABIN_CODES'], $cabinMap);

                $payload = [
                    'PASSENGER_EXTERNAL_ID' => $passengerExternalId,
                    'FULL_NAME' => $passengerData['FULL_NAME'],
                    'SEX' => $passengerData['SEX'],
                    'AGE' => $passengerData['AGE'],
                    'SIBSP' => $passengerData['SIBSP'],
                    'PARCH' => $passengerData['PARCH'],
                    'FARE' => $passengerData['FARE'],
                    'SURVIVED' => $passengerData['SURVIVED'],
                    'TICKET_ID' => $ticketId,
                    'PCLASS_ELEMENT_ID' => $classElementId,
                    'EMBARKED_ELEMENT_ID' => $embarkedElementId,
                    'CABIN_DECK_ELEMENT_ID' => $cabinDeckElementId,
                    'CABIN_RAW' => $passengerData['CABIN_RAW'],
                ];

                $result = PassengersTable::add($payload);

                if (!($result instanceof AddResult) || !$result->isSuccess()) {
                    throw new RuntimeException(implode('; ', $this->extractErrors($result)));
                }

                $passengerId = (int)$result->getId();
                $this->linkPassengerCabins($passengerId, $passengerData['CABIN_CODES'], $cabinMap);

                ++$created;
            }

            $connection->commitTransaction();
        } catch (\Throwable $exception) {
            $this->rollbackTransaction($connection);
            $errors[] = $exception->getMessage();
        }

        return [
            'success' => $errors === [],
            'total_rows' => count($rows),
            'unique_passengers' => count($passengers),
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Возвращает список пассажиров, подготовленных для `PassengersTable::add()`.
     *
     * @return list<array{
     *   PASSENGER_EXTERNAL_ID: int,
     *   FULL_NAME: string,
     *   SEX: string,
     *   AGE: float|null,
     *   SIBSP: int,
     *   PARCH: int,
     *   FARE: float,
     *   SURVIVED: int,
     *   TICKET_KEY: string,
     *   PCLASS_VALUE: int,
     *   EMBARKED_CODE: string|null,
     *   CABIN_RAW: string|null,
     *   CABIN_CODES: list<string>
     * }>
     */
    public function collectUniquePassengers(string $csvPath): array
    {
        $rows = $this->parser->parse($csvPath);

        return $this->collectUniquePassengersFromRows($rows);
    }

    /**
     * @param list<array{
     *   passenger_id: int,
     *   survived: int,
     *   pclass: int,
     *   name: string,
     *   sex: string,
     *   age: float|null,
     *   sibsp: int,
     *   parch: int,
     *   ticket: string,
     *   fare: float,
     *   cabin: string|null,
     *   embarked: string|null
     * }> $rows
     *
     * @return list<array{
     *   PASSENGER_EXTERNAL_ID: int,
     *   FULL_NAME: string,
     *   SEX: string,
     *   AGE: float|null,
     *   SIBSP: int,
     *   PARCH: int,
     *   FARE: float,
     *   SURVIVED: int,
     *   TICKET_KEY: string,
     *   PCLASS_VALUE: int,
     *   EMBARKED_CODE: string|null,
     *   CABIN_RAW: string|null,
     *   CABIN_CODES: list<string>
     * }>
     */
    private function collectUniquePassengersFromRows(array $rows): array
    {
        $passengers = [];

        foreach ($rows as $row) {
            $passengerId = (int)$row['passenger_id'];
            $ticketData = $this->ticketsImporter->normalizeTicket((string)$row['ticket']);
            $cabinCodes = $this->cabinParser->getCabinCodes((string)($row['cabin'] ?? ''));

            $passengers[$passengerId] = [
                'PASSENGER_EXTERNAL_ID' => $passengerId,
                'FULL_NAME' => trim((string)$row['name']),
                'SEX' => trim((string)$row['sex']),
                'AGE' => $row['age'],
                'SIBSP' => (int)$row['sibsp'],
                'PARCH' => (int)$row['parch'],
                'FARE' => (float)$row['fare'],
                'SURVIVED' => (int)$row['survived'],
                'TICKET_KEY' => $this->buildTicketKey($ticketData['prefix'], $ticketData['number']),
                'PCLASS_VALUE' => (int)$row['pclass'],
                'EMBARKED_CODE' => $this->normalizeEmbarkedCode($row['embarked'] ?? null),
                'CABIN_RAW' => $this->normalizeOptionalString($row['cabin'] ?? null),
                'CABIN_CODES' => $cabinCodes,
            ];
        }

        return array_values($passengers);
    }

    private function normalizeEmbarkedCode(mixed $embarkedCode): ?string
    {
        $embarkedCode = trim((string)$embarkedCode);

        if ($embarkedCode === '') {
            return null;
        }

        return strtoupper($embarkedCode);
    }

    private function normalizeOptionalString(mixed $value): ?string
    {
        $value = trim((string)$value);

        return $value === '' ? null : $value;
    }

    private function buildTicketKey(string $prefix, string $number): string
    {
        return strtoupper($prefix . '|' . $number);
    }

    /**
     * @return array<string, int>
     */
    private function loadTicketMap(): array
    {
        $rows = TicketsTable::getList([
            'select' => ['ID', 'TICKET_PREFIX', 'TICKET_NUMBER'],
        ])->fetchAll();

        $map = [];
        foreach ($rows as $row) {
            $map[$this->buildTicketKey((string)$row['TICKET_PREFIX'], (string)$row['TICKET_NUMBER'])] = (int)$row['ID'];
        }

        return $map;
    }

    /**
     * @return array<string, int>
     */
    private function loadClassMap(): array
    {
        $entityClass = TitanicClassesIblock::getEntityDataClass();
        $rows = $entityClass::getList([
            'select' => ['ID', 'CODE'],
        ])->fetchAll();

        $map = [];
        foreach ($rows as $row) {
            $code = (string)$row['CODE'];
            if (in_array($code, ['first', 'second', 'third'], true)) {
                $map[$code] = (int)$row['ID'];
            }
        }

        return $map;
    }

    /**
     * @return array<string, int>
     */
    private function loadPortMap(): array
    {
        $entityClass = TitanicPortsIblock::getEntityDataClass();
        $rows = $entityClass::getList([
            'select' => ['ID', 'CODE'],
        ])->fetchAll();

        $map = [];
        foreach ($rows as $row) {
            $map[(string)$row['CODE']] = (int)$row['ID'];
        }

        return $map;
    }

    /**
     * @param list<string> $cabinCodes
     *
     * @return array<string, array{ID: int, DECK_ELEMENT_ID: int}>
     */
    private function loadCabinMap(array $cabinCodes): array
    {
        $cabinCodes = array_values(array_filter(array_unique($cabinCodes), static fn (string $code): bool => $code !== ''));

        if ($cabinCodes === []) {
            return [];
        }

        $rows = CabinsTable::getList([
            'select' => ['ID', 'CABIN_CODE', 'DECK_ELEMENT_ID'],
            'filter' => [
                '@CABIN_CODE' => $cabinCodes,
            ],
        ])->fetchAll();

        $map = [];
        foreach ($rows as $row) {
            $map[(string)$row['CABIN_CODE']] = [
                'ID' => (int)$row['ID'],
                'DECK_ELEMENT_ID' => (int)$row['DECK_ELEMENT_ID'],
            ];
        }

        return $map;
    }

    /**
     * @param list<int> $passengerExternalIds
     *
     * @return array<string, bool>
     */
    private function loadExistingPassengers(array $passengerExternalIds): array
    {
        $passengerExternalIds = array_values(array_filter(array_unique(array_map(
            static fn (int|string $passengerExternalId): int => (int)$passengerExternalId,
            $passengerExternalIds
        )), static fn (int $passengerExternalId): bool => $passengerExternalId > 0));

        if ($passengerExternalIds === []) {
            return [];
        }

        $rows = PassengersTable::getList([
            'select' => ['PASSENGER_EXTERNAL_ID'],
            'filter' => [
                '@PASSENGER_EXTERNAL_ID' => $passengerExternalIds,
            ],
        ])->fetchAll();

        $existing = [];
        foreach ($rows as $row) {
            $existing[(string)$row['PASSENGER_EXTERNAL_ID']] = true;
        }

        return $existing;
    }

    /**
     * @param list<array{
     *   PASSENGER_EXTERNAL_ID: int,
     *   FULL_NAME: string,
     *   SEX: string,
     *   AGE: float|null,
     *   SIBSP: int,
     *   PARCH: int,
     *   FARE: float,
     *   SURVIVED: int,
     *   TICKET_KEY: string,
     *   PCLASS_VALUE: int,
     *   EMBARKED_CODE: string|null,
     *   CABIN_RAW: string|null,
     *   CABIN_CODES: list<string>
     * }> $passengers
     *
     * @return list<string>
     */
    private function collectCabinCodesFromPassengers(array $passengers): array
    {
        $cabinCodes = [];

        foreach ($passengers as $passenger) {
            foreach ($passenger['CABIN_CODES'] as $cabinCode) {
                $cabinCodes[$cabinCode] = $cabinCode;
            }
        }

        return array_values($cabinCodes);
    }

    /**
     * @param array<string, int> $ticketMap
     */
    private function resolveTicketId(string $ticketKey, array $ticketMap): int
    {
        if (isset($ticketMap[$ticketKey])) {
            return $ticketMap[$ticketKey];
        }

        throw new RuntimeException(sprintf('Не найден билет %s.', $ticketKey));
    }

    /**
     * @param array<string, int> $classMap
     */
    private function resolveClassElementId(int $pclass, array $classMap): int
    {
        $classCode = match ($pclass) {
            1 => 'first',
            2 => 'second',
            3 => 'third',
            default => throw new RuntimeException(sprintf('Неизвестный класс пассажира: %d.', $pclass)),
        };

        if (isset($classMap[$classCode])) {
            return $classMap[$classCode];
        }

        throw new RuntimeException(sprintf('Не найден элемент инфоблока для класса пассажира: %s.', $classCode));
    }

    /**
     * @param array<string, int> $portMap
     */
    private function resolvePortElementId(?string $embarkedCode, array $portMap): ?int
    {
        if ($embarkedCode === null) {
            return null;
        }

        if (isset($portMap[$embarkedCode])) {
            return $portMap[$embarkedCode];
        }

        if (isset($portMap['unknown'])) {
            return $portMap['unknown'];
        }

        throw new RuntimeException(sprintf('Не найден порт посадки для кода "%s".', $embarkedCode));
    }

    /**
     * @param list<string> $cabinCodes
     * @param array<string, array{ID: int, DECK_ELEMENT_ID: int}> $cabinMap
     */
    private function resolveCabinDeckElementId(array $cabinCodes, array $cabinMap): ?int
    {
        if ($cabinCodes === []) {
            return null;
        }

        foreach ($cabinCodes as $cabinCode) {
            if (isset($cabinMap[$cabinCode])) {
                return $cabinMap[$cabinCode]['DECK_ELEMENT_ID'];
            }
        }

        throw new RuntimeException(sprintf(
            'Не найдена каюта в таблице CabinsTable: %s.',
            implode(', ', $cabinCodes)
        ));
    }

    /**
     * @param array<string, array{ID: int, DECK_ELEMENT_ID: int}> $cabinMap
     * @param list<string> $cabinCodes
     */
    private function linkPassengerCabins(int $passengerId, array $cabinCodes, array $cabinMap): void
    {
        foreach ($cabinCodes as $cabinCode) {
            if (!isset($cabinMap[$cabinCode])) {
                throw new RuntimeException(sprintf('Не найдена каюта %s для связи с пассажиром.', $cabinCode));
            }

            $result = PassengerCabinTable::add([
                'PASSENGER_ID' => $passengerId,
                'CABIN_ID' => $cabinMap[$cabinCode]['ID'],
            ]);

            if ($result instanceof AddResult && $result->isSuccess()) {
                continue;
            }

            throw new RuntimeException(implode('; ', $this->extractErrors($result)));
        }
    }

    /**
     * @return list<string>
     */
    private function extractErrors(mixed $result): array
    {
        if ($result instanceof AddResult) {
            return $result->getErrorMessages();
        }

        return ['Не удалось добавить пассажира в таблицу.'];
    }

    private function rollbackTransaction(Connection $connection): void
    {
        try {
            $connection->rollbackTransaction();
        } catch (\Throwable) {
            // Если откат завершится неудачей, сохраняем первичную ошибку импорта.
        }
    }
}
