<?php

namespace App\Support;

final class Money
{
    public static function format(int $cents): string
    {
        return '€ ' . number_format($cents / 100, 2, ',', '.');
    }

    /** "12,50" of "12.50" → 1250 */
    public static function parse(string|int|float|null $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }
        $v = str_replace([' ', '€'], '', (string) $value);
        if (str_contains($v, ',')) {
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        }
        return (int) round(((float) $v) * 100);
    }
}
