<?php

namespace App\Services;

use App\Support\Geo;

/**
 * Zet stops in een korte volgorde (start bij de werkplaats, eind terug).
 * Nearest neighbour + 2-opt op hemelsbrede afstand. Voor de pilot ruim voldoende;
 * later kan een OSRM-afstandsmatrix de afstandsfunctie vervangen.
 */
class RouteOptimizer
{
    /**
     * @param  array<int, array{id:int|string, lat:float, lng:float}>  $stops
     * @return array{order: array<int, int|string>, meters: float}
     */
    public function optimize(array $stops, ?float $startLat = null, ?float $startLng = null): array
    {
        if (count($stops) === 0) {
            return ['order' => [], 'meters' => 0];
        }
        $start = ($startLat !== null && $startLng !== null) ? ['lat' => $startLat, 'lng' => $startLng] : $stops[0];
        $d = fn ($a, $b) => Geo::distance($a['lat'], $a['lng'], $b['lat'], $b['lng']);

        $left = array_values($stops);
        $route = [];
        $cur = $start;
        while ($left) {
            $best = 0;
            $bestD = INF;
            foreach ($left as $i => $s) {
                $dist = $d($cur, $s);
                if ($dist < $bestD) {
                    $bestD = $dist;
                    $best = $i;
                }
            }
            $cur = $left[$best];
            $route[] = $cur;
            array_splice($left, $best, 1);
        }

        $len = function (array $r) use ($start, $d) {
            $s = $d($start, $r[0]);
            for ($i = 1; $i < count($r); $i++) {
                $s += $d($r[$i - 1], $r[$i]);
            }
            return $s + $d(end($r), $start);
        };

        $improved = true;
        while ($improved) {
            $improved = false;
            for ($i = 0; $i < count($route) - 1; $i++) {
                for ($k = $i + 1; $k < count($route); $k++) {
                    $new = array_merge(array_slice($route, 0, $i), array_reverse(array_slice($route, $i, $k - $i + 1)), array_slice($route, $k + 1));
                    if ($len($new) < $len($route) - 0.01) {
                        $route = $new;
                        $improved = true;
                    }
                }
            }
        }

        return ['order' => array_column($route, 'id'), 'meters' => $len($route)];
    }
}
