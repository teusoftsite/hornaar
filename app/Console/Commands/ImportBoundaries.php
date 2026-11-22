<?php

namespace App\Console\Commands;

use App\Models\MunicipalityArea;
use App\Support\Geo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/** Gemeentegrenzen (CBS, via PDOK) importeren als GeoJSON. Jaarlijks draaien rond 1 januari (herindelingen). */
class ImportBoundaries extends Command
{
    protected $signature = 'grenzen:import {bron? : Pad of URL naar GeoJSON (WGS84). Leeg = PDOK CBS gebiedsindelingen} {--jaar= : Jaar van de indeling}';

    protected $description = 'Importeer gemeentegrenzen voor het koppelen van meldingen aan gemeenten';

    public function handle(): int
    {
        $year = (int) ($this->option('jaar') ?: now()->year);
        $src = $this->argument('bron') ?: "https://service.pdok.nl/cbs/gebiedsindelingen/{$year}/wfs/v1_0?request=GetFeature&service=WFS&version=2.0.0&typeName=gebiedsindelingen:gemeente_gegeneraliseerd&outputFormat=json&srsName=EPSG:4326";

        $this->info("Ophalen: {$src}");
        $json = str_starts_with($src, 'http') ? Http::timeout(120)->get($src)->throw()->body() : file_get_contents($src);
        $features = json_decode($json, true)['features'] ?? [];
        if (! $features) {
            $this->error('Geen features gevonden.');
            return self::FAILURE;
        }

        DB::transaction(function () use ($features, $year) {
            MunicipalityArea::query()->delete();
            foreach ($features as $f) {
                $p = $f['properties'] ?? [];
                $code = $p['statcode'] ?? $p['gemeentecode'] ?? null;
                $name = $p['statnaam'] ?? $p['gemeentenaam'] ?? $code;
                if (! $code || empty($f['geometry'])) {
                    continue;
                }
                [$minLat, $maxLat, $minLng, $maxLng] = Geo::bbox($f['geometry']);
                MunicipalityArea::create([
                    'cbs_code' => $code, 'name' => $name, 'year' => $year,
                    'min_lat' => $minLat, 'max_lat' => $maxLat, 'min_lng' => $minLng, 'max_lng' => $maxLng,
                    'geometry' => json_encode($f['geometry']),
                ]);
            }
        });

        $this->info(MunicipalityArea::count() . ' gemeenten geïmporteerd.');
        return self::SUCCESS;
    }
}
