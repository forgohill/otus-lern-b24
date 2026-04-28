<?php

declare(strict_types=1);

namespace Models\Titanic\Service;

/**
 * Разбирает значение поля Cabin из Titanic CSV.
 */
final class TitanicCabinParser
{
    private const KNOWN_DECK_CODES = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'T'];

    /**
     * Преобразует сырое значение Cabin в список кают и палуб.
     *
     * @param string $cabinRaw
     * @return list<array{
     *   CABIN_CODE: string,
     *   DECK_CODE: string
     * }>
     */
    public function parse(string $cabinRaw): array
    {
        $tokens = $this->tokenize($cabinRaw);

        if ($tokens === []) {
            return [];
        }

        if ($this->isDeckAndCabinRecord($tokens)) {
            return [[
                'CABIN_CODE' => $tokens[1],
                'DECK_CODE' => $tokens[0],
            ]];
        }

        $cabins = [];
        foreach ($tokens as $token) {
            $cabins[$token] = [
                'CABIN_CODE' => $token,
                'DECK_CODE' => $this->detectDeckCode($token),
            ];
        }

        return array_values($cabins);
    }

    /**
     * Возвращает только коды кают из значения Cabin.
     *
     * @param string $cabinRaw
     * @return list<string>
     */
    public function getCabinCodes(string $cabinRaw): array
    {
        return array_column($this->parse($cabinRaw), 'CABIN_CODE');
    }

    /**
     * Разбивает сырое значение Cabin на отдельные токены.
     *
     * @param string $cabinRaw
     * @return list<string>
     */
    private function tokenize(string $cabinRaw): array
    {
        $cabinRaw = trim($cabinRaw);

        if ($cabinRaw === '') {
            return [];
        }

        $tokens = preg_split('/\s+/u', $cabinRaw);

        if ($tokens === false) {
            return [];
        }

        $normalized = [];
        foreach ($tokens as $token) {
            $token = $this->normalizeCabinToken((string)$token);
            if ($token !== '') {
                $normalized[] = $token;
            }
        }

        return $normalized;
    }

    /**
     * Нормализует один токен каюты.
     *
     * @param string $token
     * @return string
     */
    private function normalizeCabinToken(string $token): string
    {
        $token = strtoupper(trim($token));
        $token = preg_replace('/[^A-Z0-9]+/i', '', $token) ?? '';

        return $token;
    }

    /**
     * Формат вроде `F G73` означает палубу F и каюту G73, а не две отдельные каюты.
     *
     * @param list<string> $tokens
     * @return bool
     */
    private function isDeckAndCabinRecord(array $tokens): bool
    {
        if (count($tokens) !== 2) {
            return false;
        }

        [$deckCode, $cabinCode] = $tokens;

        return $this->isKnownDeckCode($deckCode)
            && preg_match('/^[A-Z]\d+[A-Z]*$/', $cabinCode) === 1;
    }

    /**
     * Определяет палубу по коду каюты.
     *
     * @param string $cabinCode
     * @return string
     */
    private function detectDeckCode(string $cabinCode): string
    {
        $firstChar = strtoupper(substr($cabinCode, 0, 1));

        if ($this->isKnownDeckCode($firstChar)) {
            return $firstChar;
        }

        return 'unknown';
    }

    /**
     * Проверяет, известна ли палуба.
     *
     * @param string $deckCode
     * @return bool
     */
    private function isKnownDeckCode(string $deckCode): bool
    {
        return in_array($deckCode, self::KNOWN_DECK_CODES, true);
    }
}
