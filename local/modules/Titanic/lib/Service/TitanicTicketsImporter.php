<?php

declare(strict_types=1);

namespace Models\Titanic\Service;

use Bitrix\Main\Application;
use Bitrix\Main\ORM\Data\AddResult;
use Models\Titanic\Orm\TicketsTable;

/**
 * Импортирует уникальные билеты из Titanic CSV в таблицу `otus_titanic_tickets`.
 */
final class TitanicTicketsImporter
{
    private TitanicCsvParser $parser;

    /**
     * @param TitanicCsvParser|null $parser
     */
    public function __construct(?TitanicCsvParser $parser = null)
    {
        $this->parser = $parser ?? new TitanicCsvParser();
    }

    /**
     * Читает CSV, нормализует билеты и сохраняет уникальные записи в ORM-таблицу.
     *
     * @return array{
     *   success: bool,
     *   total_rows: int,
     *   unique_tickets: int,
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
        $tickets = [];

        $connection = Application::getConnection(TicketsTable::getConnectionName());

        try {
            $rows = $this->parser->parse($csvPath);
            $tickets = $this->collectUniqueTicketsFromRows($rows);

            $connection->startTransaction();

            foreach ($tickets as $ticketData) {
                if ($this->ticketExists($ticketData['TICKET_PREFIX'], $ticketData['TICKET_NUMBER'])) {
                    throw new \RuntimeException(sprintf(
                        'Билет %s %s уже существует в таблице.',
                        $ticketData['TICKET_PREFIX'],
                        $ticketData['TICKET_NUMBER']
                    ));
                }

                $result = TicketsTable::add($ticketData);

                if ($result instanceof AddResult && $result->isSuccess()) {
                    ++$created;
                    continue;
                }

                throw new \RuntimeException(
                    implode('; ', $this->extractErrors($result))
                );
            }

            $connection->commitTransaction();
        } catch (\Throwable $exception) {
            $this->rollbackTransaction($connection);
            $errors[] = $exception->getMessage();
        }

        return [
            'success' => $errors === [],
            'total_rows' => count($rows),
            'unique_tickets' => count($tickets),
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Возвращает список уникальных билетов, подготовленных для `TicketsTable::add()`.
     *
     * @return list<array{
     *   TICKET_RAW: string,
     *   TICKET_PREFIX: string,
     *   TICKET_NUMBER: string,
     *   PASSENGER_COUNT: int,
     *   FARE_TOTAL: float
     * }>
     */
    public function collectUniqueTickets(string $csvPath): array
    {
        $rows = $this->parser->parse($csvPath);

        return $this->collectUniqueTicketsFromRows($rows);
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
     *   TICKET_RAW: string,
     *   TICKET_PREFIX: string,
     *   TICKET_NUMBER: string,
     *   PASSENGER_COUNT: int,
     *   FARE_TOTAL: float
     * }>
     */
    private function collectUniqueTicketsFromRows(array $rows): array
    {
        $tickets = [];

        foreach ($rows as $row) {
            $ticket = $this->normalizeTicket((string)$row['ticket']);
            $ticketKey = $this->buildTicketKey($ticket['prefix'], $ticket['number']);

            if (!isset($tickets[$ticketKey])) {
                $tickets[$ticketKey] = [
                    'TICKET_RAW' => trim((string)$row['ticket']),
                    'TICKET_PREFIX' => $ticket['prefix'],
                    'TICKET_NUMBER' => $ticket['number'],
                    'PASSENGER_COUNT' => 0,
                    'FARE_TOTAL' => 0.0,
                ];
            }

            ++$tickets[$ticketKey]['PASSENGER_COUNT'];
            $tickets[$ticketKey]['FARE_TOTAL'] += (float)$row['fare'];
        }

        return array_values($tickets);
    }

    /**
     * Нормализует Ticket к prefix + number.
     *
     * Примеры:
     * - PC 17599 -> prefix PC, number 17599
     * - CA. 2343 -> prefix CA, number 2343
     * - 347082 -> prefix NUMERIC, number 347082
     * - STON/O2. 3101282 -> prefix STONO2, number 3101282
     *
     * @param string $ticketRaw
     * @return array{prefix: string, number: string}
     */
    public function normalizeTicket(string $ticketRaw): array
    {
        $ticketRaw = trim($ticketRaw);

        if ($ticketRaw === '') {
            throw new \InvalidArgumentException('Ticket must not be empty.');
        }

        if (preg_match('/^(\d+)$/', $ticketRaw, $matches) === 1) {
            return [
                'prefix' => 'NUMERIC',
                'number' => $matches[1],
            ];
        }

        $number = '';
        $prefixSource = $ticketRaw;

        if (preg_match('/^(.*?)(\d+)\s*$/u', $ticketRaw, $matches) === 1) {
            $prefixSource = $matches[1];
            $number = $matches[2];
        }

        $prefix = preg_replace('/[^a-z0-9]+/i', '', $prefixSource) ?? '';
        $prefix = strtoupper($prefix);

        if ($prefix === '') {
            $prefix = 'NUMERIC';
        }

        return [
            'prefix' => $prefix,
            'number' => $number,
        ];
    }

    /**
     * Собирает ключ билета из prefix и number.
     *
     * @param string $prefix
     * @param string $number
     * @return string
     */
    private function buildTicketKey(string $prefix, string $number): string
    {
        return strtoupper($prefix . '|' . $number);
    }

    /**
     * Проверяет, существует ли билет в таблице.
     *
     * @param string $prefix
     * @param string $number
     * @return bool
     */
    private function ticketExists(string $prefix, string $number): bool
    {
        $row = TicketsTable::getList([
            'select' => ['ID'],
            'filter' => [
                '=TICKET_PREFIX' => $prefix,
                '=TICKET_NUMBER' => $number,
            ],
            'limit' => 1,
        ])->fetch();

        return is_array($row);
    }

    /**
     * Извлекает сообщения об ошибках из результата ORM-операции.
     *
     * @param mixed $result
     * @return list<string>
     */
    private function extractErrors(mixed $result): array
    {
        if ($result instanceof AddResult) {
            return $result->getErrorMessages();
        }

        return ['Не удалось добавить билет в таблицу.'];
    }

    /**
     * Откатывает транзакцию и скрывает возможную ошибку отката.
     *
     * @param \Bitrix\Main\DB\Connection $connection
     * @return void
     */
    private function rollbackTransaction(\Bitrix\Main\DB\Connection $connection): void
    {
        try {
            $connection->rollbackTransaction();
        } catch (\Throwable) {
            // Если откат завершится неудачей, нам все равно нужно будет вернуть исходную ошибку импорта.
        }
    }
}
