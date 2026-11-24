<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Organisation;
use App\Models\Report;
use App\Models\User;
use App\Notifications\LeadAccepted;
use App\Notifications\LeadNoBestrijder;
use App\Notifications\LeadOffered;
use App\Support\Geo;
use App\Support\NestStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Particuliere leads: geen deelnemende gemeente, dan zoeken we de dichtstbijzijnde bestrijder.
 * Weigert hij of reageert hij niet op tijd, dan gaat de lead naar de volgende. Hij betaalt alleen bij accepteren.
 */
class LeadService
{
    public function __construct(private NestService $nests) {}

    public function request(Report $report, array $data): Lead
    {
        $nest = $report->nest;
        if (! $nest->isParticulier()) {
            throw new \DomainException('Een nest op een openbare plek gaat via de gemeente.');
        }
        if ($nest->leads()->whereIn('status', ['zoeken', 'aangeboden', 'geaccepteerd'])->exists()) {
            throw new \DomainException('Er is al een bestrijder gevraagd voor dit nest.');
        }

        $lead = Lead::create([
            'nest_id' => $nest->id,
            'report_id' => $report->id,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? $report->reporter_email,
            'status' => 'zoeken',
            'declined_ids' => [],
        ]);
        if (! $report->reporter_email && ! empty($data['email'])) {
            $report->update(['reporter_email' => $data['email']]);
        }

        return $this->offerNext($lead);
    }

    /** Bestrijders die particuliere leads willen en het nest binnen hun werkgebied hebben, dichtstbijzijnde eerst. */
    public function candidates(Lead $lead): Collection
    {
        $nest = $lead->nest;
        $skip = $lead->declined_ids ?? [];

        return Organisation::bestrijders()
            ->where('active', true)->where('accepts_leads', true)
            ->whereNotNull('base_lat')->whereNotNull('base_lng')
            ->whereNotIn('id', $skip)
            ->get()
            ->map(fn ($o) => [$o, Geo::distance($nest->lat, $nest->lng, $o->base_lat, $o->base_lng)])
            ->filter(fn ($p) => $p[1] <= $p[0]->service_radius_km * 1000)
            ->sortBy(fn ($p) => $p[1])
            ->map(fn ($p) => $p[0])
            ->values();
    }

    public function offerNext(Lead $lead): Lead
    {
        $next = $this->candidates($lead)->first();

        if (! $next) {
            $lead->update(['status' => 'geen_bestrijder', 'offered_to_id' => null, 'offered_at' => null, 'expires_at' => null]);
            if ($lead->email) {
                Notification::route('mail', $lead->email)->notify(new LeadNoBestrijder($lead));
            }
            Notification::route('mail', config('meldhoornaar.contact_to'))->notify(new LeadNoBestrijder($lead, true));
            return $lead;
        }

        $lead->update([
            'status' => 'aangeboden',
            'offered_to_id' => $next->id,
            'offered_at' => now(),
            'expires_at' => now()->addHours(config('meldhoornaar.lead_response_hours')),
        ]);

        $to = $next->notify_email ?: $next->invoice_email;
        if ($to) {
            Notification::route('mail', $to)->notify(new LeadOffered($lead));
        }

        return $lead;
    }

    private function own(Lead $lead, User $by): void
    {
        if ($lead->status !== 'aangeboden' || $lead->offered_to_id !== $by->organisation_id) {
            throw new \DomainException('Deze lead is niet (meer) aan u aangeboden.');
        }
    }

    public function accept(Lead $lead, User $by): Lead
    {
        $this->own($lead, $by);
        if ($lead->expires_at && $lead->expires_at->isPast()) {
            $this->decline($lead, null, 'verlopen');
            throw new \DomainException('De reactietijd is verstreken; de lead is doorgezet.');
        }

        DB::transaction(function () use ($lead, $by) {
            $lead->update([
                'status' => 'geaccepteerd',
                'bestrijder_id' => $by->organisation_id,
                'accepted_at' => now(),
                'fee_cents' => config('meldhoornaar.lead_fee_cents'),
            ]);
            $this->nests->transition($lead->nest, NestStatus::OPDRACHT, $by, 'Particulier: ' . $by->organisation->name . ' komt langs', false);
        });

        if ($lead->email) {
            Notification::route('mail', $lead->email)->notify(new LeadAccepted($lead->fresh(['bestrijder', 'report'])));
        }

        return $lead;
    }

    public function decline(Lead $lead, ?User $by, string $reason = 'geweigerd'): Lead
    {
        if ($by) {
            $this->own($lead, $by);
        }
        $declined = array_values(array_unique(array_merge($lead->declined_ids ?? [], [$lead->offered_to_id])));
        $lead->update(['declined_ids' => $declined]);

        return $this->offerNext($lead);
    }

    /** Leads waarop niet op tijd is gereageerd, doorzetten naar de volgende bestrijder. */
    public function expireOverdue(): int
    {
        $n = 0;
        foreach (Lead::where('status', 'aangeboden')->where('expires_at', '<', now())->get() as $lead) {
            $this->decline($lead, null, 'geen reactie');
            $n++;
        }
        return $n;
    }

    public function complete(Lead $lead, User $by, string $result): Lead
    {
        if ($lead->status !== 'geaccepteerd' || $lead->bestrijder_id !== $by->organisation_id) {
            throw new \DomainException('Deze lead kunt u niet afronden.');
        }
        DB::transaction(function () use ($lead, $by, $result) {
            $lead->update(['status' => 'afgerond', 'result' => $result, 'closed_at' => now()]);
            $to = $result === 'bestreden' ? NestStatus::BESTREDEN : NestStatus::AFGEWEZEN;
            $this->nests->transition($lead->nest, $to, $by, 'Particulier: ' . ($result === 'bestreden' ? 'nest bestreden' : 'geen nest'));
        });
        return $lead;
    }
}
