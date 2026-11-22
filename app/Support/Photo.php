<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

final class Photo
{
    /** Ondertekende URL, 1 uur geldig. */
    public static function url(?string $path): ?string
    {
        return $path ? URL::temporarySignedRoute('foto', now()->addHour(), ['path' => $path]) : null;
    }
}
