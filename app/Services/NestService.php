<?php

namespace App\Services;

use App\Models\Nest;
use App\Models\Report;
use App\Models\StatusEvent;
use App\Models\User;
use App\Notifications\NestStatusChanged;
use App\Support\Geo;
use App\Support\NestStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class NestService
{
    public const MERGE_RADIUS_M = 25;

    public function transition(Nest $nest, string $to, ?User $by = null, ?string $note = null, bool $notify = true): Nest
    {
        $from = $nest->status;
        if ($from === $to) {
            return $nest;
        }
        if (! NestStatus::canMove($from, $to)) {
            throw new \DomainException('Statuswissel van ' . NestStatus::label($from) . ' naar ' . NestStatus::label($to) . ' is niet toegestaan.');
        }

        DB::transaction(function () use ($nest, $from, $to, $by, $note) {
            $nest->update(['status' => $to]);
            StatusEvent::create([
                'nest_id' => $nest->id, 'from' => $from, 'to' => $to,
                'user_id' => $by?->id, 'note' => $note,
            ]);
        });

        if ($notify) {
            $this->notifyReporters($nest, $to);
        }

        return $nest;
    }

    /** Statusmail naar alle melders die een e-mail achterlieten (alleen bij relevante wissels). */
    public function notifyReporters(Nest $nest, string $to): void
    {
        if (! in_array($to, [NestStatus::GOEDGEKEURD, NestStatus::AFGEWEZEN, NestStatus::INGEPLAND, NestStatus::BESTREDEN], true)) {
            return;
        }
        $nest->reports()->whereNotNull('reporter_email')->get()
            ->each(fn (Report $r) => Notification::route('mail', $r->reporter_email)->notify(new NestStatusChanged($nest, $r, $to)));
    }

    /** Open nesten binnen 25 m van dit nest (kandidaten om samen te voegen). */
    public function duplicateCandidates(Nest $nest)
    {
        $dLat = self::MERGE_RADIUS_M / 111000;
        $dLng = $dLat / max(cos(deg2rad($nest->lat)), 0.01);

        return Nest::query()
            ->where('id', '!=', $nest->id)
            ->where('gemeente_id', $nest->gemeente_id)
            ->whereIn('status', NestStatus::OPEN)
            ->whereBetween('lat', [$nest->lat - $dLat, $nest->lat + $dLat])
            ->whereBetween('lng', [$nest->lng - $dLng, $nest->lng + $dLng])
            ->get()
            ->filter(fn (Nest $n) => Geo::distance($nest->lat, $nest->lng, $n->lat, $n->lng) <= self::MERGE_RADIUS_M)
            ->values();
    }

    /** Voeg $duplicate samen in $target: meldingen verhuizen, duplicate wordt 'samengevoegd'. */
    public function merge(Nest $duplicate, Nest $target, ?User $by = null): void
    {
        DB::transaction(function () use ($duplicate, $target, $by) {
            Report::where('nest_id', $duplicate->id)->update(['nest_id' => $target->id]);
            $duplicate->update(['merged_into_id' => $target->id]);
            $this->transition($duplicate, NestStatus::SAMENGEVOEGD, $by, 'Samengevoegd met nest #' . $target->id, false);
            StatusEvent::create([
                'nest_id' => $target->id, 'from' => $target->status, 'to' => $target->status,
                'user_id' => $by?->id, 'note' => 'Melding van nest #' . $duplicate->id . ' toegevoegd',
            ]);
        });
    }
}
