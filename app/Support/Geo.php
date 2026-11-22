<?php

namespace App\Support;

final class Geo
{
    /** Afstand in meters tussen twee coördinaten (haversine). */
    public static function distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return 2 * $r * asin(min(1, sqrt($a)));
    }

    /** Ligt punt (lng, lat) in een GeoJSON Polygon/MultiPolygon? */
    public static function inGeometry(float $lat, float $lng, array $geometry): bool
    {
        $polygons = match ($geometry['type'] ?? null) {
            'Polygon' => [$geometry['coordinates']],
            'MultiPolygon' => $geometry['coordinates'],
            default => [],
        };
        foreach ($polygons as $rings) {
            if (! self::inRing($lng, $lat, $rings[0])) {
                continue;
            }
            $inHole = false;
            foreach (array_slice($rings, 1) as $hole) {
                if (self::inRing($lng, $lat, $hole)) {
                    $inHole = true;
                    break;
                }
            }
            if (! $inHole) {
                return true;
            }
        }
        return false;
    }

    private static function inRing(float $x, float $y, array $ring): bool
    {
        $inside = false;
        $n = count($ring);
        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            [$xi, $yi] = $ring[$i];
            [$xj, $yj] = $ring[$j];
            if ((($yi > $y) !== ($yj > $y)) && ($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 1e-12) + $xi)) {
                $inside = ! $inside;
            }
        }
        return $inside;
    }

    /** [minLat, maxLat, minLng, maxLng] van een GeoJSON-geometrie. */
    public static function bbox(array $geometry): array
    {
        $minLat = $minLng = INF;
        $maxLat = $maxLng = -INF;
        $walk = function ($c) use (&$walk, &$minLat, &$maxLat, &$minLng, &$maxLng) {
            if (is_array($c) && count($c) >= 2 && is_numeric($c[0]) && is_numeric($c[1])) {
                $minLng = min($minLng, $c[0]); $maxLng = max($maxLng, $c[0]);
                $minLat = min($minLat, $c[1]); $maxLat = max($maxLat, $c[1]);
                return;
            }
            foreach ($c as $sub) {
                $walk($sub);
            }
        };
        $walk($geometry['coordinates']);
        return [$minLat, $maxLat, $minLng, $maxLng];
    }
}
