<?php

namespace App\Support;

final class NestStatus
{
    public const GEMELD = 'gemeld';
    public const GOEDGEKEURD = 'goedgekeurd';
    public const AFGEWEZEN = 'afgewezen';
    public const SAMENGEVOEGD = 'samengevoegd';
    public const OPDRACHT = 'opdracht_gegeven';
    public const INGEPLAND = 'ingepland';
    public const GEEN_TOEGANG = 'geen_toegang';
    public const BESTREDEN = 'bestreden';
    public const GEFACTUREERD = 'gefactureerd';

    public const LABELS = [
        self::GEMELD => 'Gemeld',
        self::GOEDGEKEURD => 'Goedgekeurd',
        self::AFGEWEZEN => 'Afgewezen',
        self::SAMENGEVOEGD => 'Samengevoegd',
        self::OPDRACHT => 'Opdracht gegeven',
        self::INGEPLAND => 'Ingepland',
        self::GEEN_TOEGANG => 'Geen toegang',
        self::BESTREDEN => 'Bestreden',
        self::GEFACTUREERD => 'Gefactureerd',
    ];

    /** Toegestane overgangen. */
    public const FLOW = [
        self::GEMELD => [self::GOEDGEKEURD, self::AFGEWEZEN, self::SAMENGEVOEGD],
        self::GOEDGEKEURD => [self::OPDRACHT, self::AFGEWEZEN],
        self::OPDRACHT => [self::INGEPLAND, self::GOEDGEKEURD],
        self::INGEPLAND => [self::BESTREDEN, self::GEEN_TOEGANG, self::AFGEWEZEN, self::OPDRACHT],
        self::GEEN_TOEGANG => [self::INGEPLAND, self::OPDRACHT],
        self::BESTREDEN => [self::GEFACTUREERD],
        self::AFGEWEZEN => [],
        self::SAMENGEVOEGD => [],
        self::GEFACTUREERD => [],
    ];

    /** Statussen waarin een nest nog "open" is (voor samenvoegen). */
    public const OPEN = [self::GEMELD, self::GOEDGEKEURD, self::OPDRACHT, self::INGEPLAND, self::GEEN_TOEGANG];

    public static function label(string $s): string
    {
        return self::LABELS[$s] ?? $s;
    }

    public static function canMove(string $from, string $to): bool
    {
        return in_array($to, self::FLOW[$from] ?? [], true);
    }
}
