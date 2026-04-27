<?php

declare(strict_types=1);

namespace Models\Titanic\Service;

use InvalidArgumentException;
use RuntimeException;

/**
 * Парсер CSV для набора данных Titanic.
 *
 * Класс только читает исходный файл и возвращает нормализованные строки.
 * Он ничего не знает про ORM-таблицы и инфоблоки.
 */
final class TitanicCsvParser
{
    /**
     * Соответствие заголовков CSV нормализованным ключам парсера.
     *
     * @var array<string, string>
     */
    private const HEADER_MAP = [
        'PassengerId' => 'passenger_id',
        'Survived' => 'survived',
        'Pclass' => 'pclass',
        'Name' => 'name',
        'Sex' => 'sex',
        'Age' => 'age',
        'SibSp' => 'sibsp',
        'Parch' => 'parch',
        'Ticket' => 'ticket',
        'Fare' => 'fare',
        'Cabin' => 'cabin',
        'Embarked' => 'embarked',
    ];

    private string $delimiter;

    private string $enclosure;

    private string $escape;

    public function __construct(string $delimiter = ',', string $enclosure = '"', string $escape = '\\')
    {
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape;
    }

    /**
     * Читает CSV-файл и возвращает нормализованные строки.
     *
     * @return list<array{
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
     * }>
     */
    public function parse(string $filePath): array
    {
        $handle = $this->openFile($filePath);
        try {
            $headers = $this->readHeaders($handle, $filePath);
            $rows = [];

            while (($data = fgetcsv($handle, 0, $this->delimiter, $this->enclosure, $this->escape)) !== false) {
                if ($this->isEmptyRow($data)) {
                    continue;
                }

                $rows[] = $this->normalizeRow($headers, $data, $filePath);
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    /**
     * Открывает CSV-файл для чтения.
     *
     * @return resource
     */
    private function openFile(string $filePath)
    {
        if ($filePath === '') {
            throw new InvalidArgumentException('CSV file path must not be empty.');
        }

        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new RuntimeException(sprintf('CSV file "%s" is not readable.', $filePath));
        }

        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            throw new RuntimeException(sprintf('Unable to open CSV file "%s".', $filePath));
        }

        return $handle;
    }

    /**
     * Читает строку заголовков CSV-файла.
     *
     * @param resource $handle
     *
     * @return list<string>
     */
    private function readHeaders($handle, string $filePath): array
    {
        $headers = fgetcsv($handle, 0, $this->delimiter, $this->enclosure, $this->escape);

        if ($headers === false) {
            throw new RuntimeException(sprintf('CSV file "%s" does not contain a header row.', $filePath));
        }

        $normalized = [];
        foreach ($headers as $header) {
            $normalized[] = $this->normalizeHeader((string)$header);
        }

        return $normalized;
    }

    /**
     * Нормализует строку CSV в удобный массив для дальнейшей обработки.
     *
     * @param list<string> $headers
     * @param list<string|null> $data
     *
     * @return array{
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
     * }
     */
    private function normalizeRow(array $headers, array $data, string $filePath): array
    {
        $row = [];

        foreach ($headers as $index => $header) {
            $row[$header] = $data[$index] ?? null;
        }

        $requiredFields = ['passenger_id', 'survived', 'pclass', 'name', 'sex', 'sibsp', 'parch', 'ticket', 'fare'];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $row) || $this->isEmptyValue($row[$field])) {
                throw new RuntimeException(sprintf('CSV file "%s" is missing required column "%s".', $filePath, $field));
            }
        }

        return [
            'passenger_id' => $this->toInt($row['passenger_id']),
            'survived' => $this->toInt($row['survived']),
            'pclass' => $this->toInt($row['pclass']),
            'name' => $this->toString($row['name']),
            'sex' => $this->toString($row['sex']),
            'age' => $this->toNullableFloat($row['age'] ?? null),
            'sibsp' => $this->toInt($row['sibsp']),
            'parch' => $this->toInt($row['parch']),
            'ticket' => $this->toString($row['ticket']),
            'fare' => $this->toFloat($row['fare']),
            'cabin' => $this->toNullableString($row['cabin'] ?? null),
            'embarked' => $this->toNullableString($row['embarked'] ?? null),
        ];
    }

    private function normalizeHeader(string $header): string
    {
        $header = trim($header);

        if (isset(self::HEADER_MAP[$header])) {
            return self::HEADER_MAP[$header];
        }

        $header = preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $header) ?? $header;
        $header = preg_replace('/[^a-z0-9]+/i', '_', $header) ?? $header;
        $header = strtolower(trim($header, '_'));

        return $header;
    }

    private function isEmptyRow(array $data): bool
    {
        foreach ($data as $value) {
            if ($value !== null && trim((string)$value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function isEmptyValue(mixed $value): bool
    {
        return $value === null || trim((string)$value) === '';
    }

    /**
     * Приводит значение к строке.
     */
    private function toString(mixed $value): string
    {
        return trim((string)$value);
    }

    /**
     * Приводит значение к строке или null.
     */
    private function toNullableString(mixed $value): ?string
    {
        $value = trim((string)$value);

        return $value === '' ? null : $value;
    }

    /**
     * Приводит значение к целому числу.
     */
    private function toInt(mixed $value): int
    {
        return (int)trim((string)$value);
    }

    /**
     * Приводит значение к числу с плавающей точкой.
     */
    private function toFloat(mixed $value): float
    {
        return (float)str_replace(',', '.', trim((string)$value));
    }

    /**
     * Приводит значение к числу с плавающей точкой или null.
     */
    private function toNullableFloat(mixed $value): ?float
    {
        $value = trim((string)$value);

        if ($value === '') {
            return null;
        }

        return (float)str_replace(',', '.', $value);
    }
}
