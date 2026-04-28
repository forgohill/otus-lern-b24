<?php

declare(strict_types=1);

namespace Models\Titanic\Service;

use Bitrix\Main\Application;
use Bitrix\Main\ORM\Data\AddResult;
use Models\Titanic\Orm\CabinsTable;
use Models\Titanic\Service\Iblock\TitanicCabinDecksIblock;

/**
 * Импортирует уникальные каюты из Titanic CSV в таблицу `otus_titanic_cabins`.
 */
final class TitanicCabinsImporter
{
    private TitanicCsvParser $parser;

    private TitanicCabinParser $cabinParser;

    /**
     * @param TitanicCsvParser|null $parser
     * @param TitanicCabinParser|null $cabinParser
     */
    public function __construct(?TitanicCsvParser $parser = null, ?TitanicCabinParser $cabinParser = null)
    {
        $this->parser = $parser ?? new TitanicCsvParser();
        $this->cabinParser = $cabinParser ?? new TitanicCabinParser();
    }

    /**
     * Читает CSV, разбивает каюты и сохраняет уникальные записи в ORM-таблицу.
     *
     * @return array{
     *   success: bool,
     *   total_rows: int,
     *   unique_cabins: int,
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
        $cabins = [];

        $connection = Application::getConnection(CabinsTable::getConnectionName());

        try {
            $rows = $this->parser->parse($csvPath);
            $cabins = $this->collectUniqueCabinsFromRows($rows);
            $deckMap = $this->loadDeckMap(array_column($cabins, 'DECK_CODE'));
            $existingCabins = $this->loadExistingCabins(array_column($cabins, 'CABIN_CODE'));

            $connection->startTransaction();

            foreach ($cabins as $cabinData) {
                $cabinCode = $cabinData['CABIN_CODE'];
                $deckCode = $cabinData['DECK_CODE'];

                if (isset($existingCabins[$cabinCode])) {
                    throw new \RuntimeException(sprintf('Каюта %s уже существует в таблице.', $cabinCode));
                }

                if (!isset($deckMap[$deckCode])) {
                    throw new \RuntimeException(sprintf('Не найдена палуба %s для каюты %s.', $deckCode, $cabinCode));
                }

                $cabinData['DECK_ELEMENT_ID'] = $deckMap[$deckCode];

                $result = CabinsTable::add($cabinData);

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
            'unique_cabins' => count($cabins),
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Возвращает список уникальных кают, подготовленных для `CabinsTable::add()`.
     *
     * @return list<array{
     *   CABIN_CODE: string,
     *   DECK_CODE: string
     * }>
     */
    public function collectUniqueCabins(string $csvPath): array
    {
        $rows = $this->parser->parse($csvPath);

        return $this->collectUniqueCabinsFromRows($rows);
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
     *   CABIN_CODE: string,
     *   DECK_CODE: string
     * }>
     */
    private function collectUniqueCabinsFromRows(array $rows): array
    {
        $cabins = [];

        foreach ($rows as $row) {
            foreach ($this->cabinParser->parse((string)($row['cabin'] ?? '')) as $cabin) {
                $cabins[$cabin['CABIN_CODE']] = $cabin;
            }
        }

        return array_values($cabins);
    }

    /**
     * @param list<string> $deckCodes
     *
     * @return array<string, int>
     */
    private function loadDeckMap(array $deckCodes): array
    {
        $deckCodes = array_values(array_unique(array_merge($deckCodes, ['unknown'])));
        $deckCodes = array_values(array_filter($deckCodes, static fn ($code): bool => $code !== ''));

        $entityClass = TitanicCabinDecksIblock::getEntityDataClass();
        $rows = $entityClass::getList([
            'select' => ['ID', 'CODE'],
            'filter' => [
                '@CODE' => $deckCodes,
            ],
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
     * @return array<string, bool>
     */
    private function loadExistingCabins(array $cabinCodes): array
    {
        $cabinCodes = array_values(array_filter(array_unique($cabinCodes), static fn ($code): bool => $code !== ''));

        if ($cabinCodes === []) {
            return [];
        }

        $rows = CabinsTable::getList([
            'select' => ['CABIN_CODE'],
            'filter' => [
                '@CABIN_CODE' => $cabinCodes,
            ],
        ])->fetchAll();

        $existing = [];
        foreach ($rows as $row) {
            $existing[(string)$row['CABIN_CODE']] = true;
        }

        return $existing;
    }

    /**
     * @return list<string>
     */
    private function extractErrors(mixed $result): array
    {
        if ($result instanceof AddResult) {
            return $result->getErrorMessages();
        }

        return ['Не удалось добавить каюту в таблицу.'];
    }

    /**
     * Откатывает транзакцию импорта, если это возможно.
     *
     * @param \Bitrix\Main\DB\Connection $connection
     * @return void
     */
    private function rollbackTransaction(\Bitrix\Main\DB\Connection $connection): void
    {
        try {
            $connection->rollbackTransaction();
        } catch (\Throwable) {
            // Если откат завершится неудачей, нам все равно нужно вернуть исходную ошибку импорта.
        }
    }
}
