<?php

namespace App\Services;

use App\Models\Nest;
use App\Models\Report;
use App\Models\StatusEvent;
use App\Notifications\NewNestReported;
use App\Notifications\ReportReceived;
use App\Support\NestStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class ReportService
{
    public function __construct(
        private Geocoder $geocoder,
        private MunicipalityLocator $locator,
        private PhotoStore $photos,
    ) {}

    public function create(array $data, ?UploadedFile $photo, ?string $ip): Report
    {
        $lat = (float) $data['lat'];
        $lng = (float) $data['lng'];

        $geo = $this->geocoder->reverse($lat, $lng);
        $cbs = $this->locator->cbsCodeFor($lat, $lng) ?? $geo['cbs_code'];
        $plek = ($data['plek'] ?? 'openbaar') === 'eigen' ? 'eigen' : 'openbaar';
        // Eigen terrein regelt de inwoner zelf met een bestrijder; dat gaat niet via de gemeente.
        $gemeente = $plek === 'openbaar' ? $this->locator->gemeenteForCode($cbs) : null;

        $photoPath = $photo ? $this->photos->store($photo, 'meldingen') : null;

        $report = DB::transaction(function () use ($data, $plek, $lat, $lng, $geo, $cbs, $gemeente, $photoPath, $ip) {
            $nest = Nest::create([
                'gemeente_id' => $gemeente?->id,
                'cbs_code' => $cbs,
                'lat' => $lat,
                'lng' => $lng,
                'address' => $geo['address'],
                'location_type' => $data['location_type'] ?? null,
                'plek' => $plek,
                'height_m' => $data['height_m'] ?? null,
                'status' => NestStatus::GEMELD,
            ]);
            StatusEvent::create(['nest_id' => $nest->id, 'from' => null, 'to' => NestStatus::GEMELD, 'note' => 'Melding via website']);

            return Report::create([
                'nest_id' => $nest->id,
                'source' => 'web',
                'photo_path' => $photoPath,
                'note' => $data['note'] ?? null,
                'reporter_email' => $data['email'] ?? null,
                'token' => Str::random(32),
                'ip_hash' => $ip ? hash('sha256', $ip . config('app.key')) : null,
                'lat' => $lat,
                'lng' => $lng,
            ]);
        });

        if ($report->reporter_email) {
            Notification::route('mail', $report->reporter_email)->notify(new ReportReceived($report));
        }
        if ($gemeente?->notify_email) {
            Notification::route('mail', $gemeente->notify_email)->notify(new NewNestReported($report->nest));
        }

        return $report;
    }
}
