<?php

namespace App\Services;

use App\Models\MunicipalityArea;
use App\Models\Organisation;
use App\Support\Geo;

/** Bepaalt bij welke gemeente een coördinaat hoort. */
class MunicipalityLocator
{
    /** CBS-code uit de geïmporteerde grenzen, of null. */
    public function cbsCodeFor(float $lat, float $lng): ?string
    {
        $candidates = MunicipalityArea::query()
            ->where('min_lat', '<=', $lat)->where('max_lat', '>=', $lat)
            ->where('min_lng', '<=', $lng)->where('max_lng', '>=', $lng)
            ->orderByDesc('year')
            ->get();

        foreach ($candidates as $area) {
            if (Geo::inGeometry($lat, $lng, json_decode($area->geometry, true) ?? [])) {
                return $area->cbs_code;
            }
        }

        return null;
    }

    public function gemeenteForCode(?string $cbsCode): ?Organisation
    {
        return $cbsCode
            ? Organisation::gemeenten()->where('cbs_code', $cbsCode)->where('active', true)->first()
            : null;
    }
}
