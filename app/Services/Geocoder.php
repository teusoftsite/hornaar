<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/** PDOK Locatieserver: coördinaat → BAG-adres + gemeentecode. */
class Geocoder
{
    public const URL = 'https://api.pdok.nl/bzk/locatieserver/search/v3_1/reverse';

    /** @return array{address:?string, cbs_code:?string, gemeente:?string} */
    public function reverse(float $lat, float $lng): array
    {
        try {
            $res = Http::timeout(4)->get(self::URL, [
                'lat' => $lat, 'lon' => $lng, 'rows' => 1, 'type' => 'adres', 'fl' => 'weergavenaam,gemeentecode,gemeentenaam',
            ]);
            $doc = $res->json('response.docs.0') ?? [];
        } catch (\Throwable) {
            $doc = [];
        }

        $code = $doc['gemeentecode'] ?? null;

        return [
            'address' => $doc['weergavenaam'] ?? null,
            'cbs_code' => $code ? 'GM' . str_pad(ltrim((string) $code, 'GM'), 4, '0', STR_PAD_LEFT) : null,
            'gemeente' => $doc['gemeentenaam'] ?? null,
        ];
    }
}
