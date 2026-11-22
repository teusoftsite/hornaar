<?php

namespace App\Support;

final class Role
{
    public const BEHEERDER = 'beheerder';
    public const GEMEENTE = 'gemeente';
    public const BESTRIJDER_EIGENAAR = 'bestrijder_eigenaar';
    public const BESTRIJDER_MEDEWERKER = 'bestrijder_medewerker';

    public const LABELS = [
        self::BEHEERDER => 'Beheerder',
        self::GEMEENTE => 'Gemeente-medewerker',
        self::BESTRIJDER_EIGENAAR => 'Bestrijder (eigenaar)',
        self::BESTRIJDER_MEDEWERKER => 'Bestrijder (medewerker)',
    ];

    /** Rollen waarvoor 2FA verplicht is. */
    public const REQUIRES_2FA = [self::BEHEERDER, self::GEMEENTE, self::BESTRIJDER_EIGENAAR];
}
