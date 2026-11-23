<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadInvoice;
use App\Notifications\LeadInvoiceSent;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

/** Maandfactuur van MeldHoornaar aan de bestrijder voor geaccepteerde particuliere leads. */
class LeadInvoiceService
{
    public function generateMonthly(CarbonInterface $runDate): array
    {
        $periodEnd = $runDate->copy()->startOfMonth()->subSecond();
        $vat = config('meldhoornaar.seller.vat_rate');
        $created = [];

        $open = Lead::whereNull('lead_invoice_id')->whereNotNull('accepted_at')
            ->where('accepted_at', '<=', $periodEnd)->where('fee_cents', '>', 0)
            ->get()->groupBy('bestrijder_id');

        foreach ($open as $bestrijderId => $leads) {
            $created[] = DB::transaction(function () use ($bestrijderId, $leads, $periodEnd, $vat) {
                $sub = (int) $leads->sum('fee_cents');
                $inv = LeadInvoice::create([
                    'bestrijder_id' => $bestrijderId,
                    'period' => $periodEnd->format('Y-m'),
                    'lead_count' => $leads->count(),
                    'subtotal_cents' => $sub,
                    'vat_rate' => $vat,
                    'vat_cents' => (int) round($sub * $vat / 100),
                    'total_cents' => $sub + (int) round($sub * $vat / 100),
                ]);
                Lead::whereIn('id', $leads->pluck('id'))->update(['lead_invoice_id' => $inv->id]);
                return $inv;
            });
        }

        return $created;
    }

    public function send(LeadInvoice $inv): LeadInvoice
    {
        if ($inv->status !== 'concept') {
            throw new \DomainException('Deze factuur is al verstuurd.');
        }
        if (! config('meldhoornaar.seller.iban') || ! config('meldhoornaar.seller.kvk')) {
            throw new \DomainException('Vul eerst KvK-nummer en IBAN van MeldHoornaar in (.env: MH_SELLER_KVK, MH_SELLER_IBAN).');
        }

        DB::transaction(function () use ($inv) {
            $year = now()->format('Y');
            $last = LeadInvoice::where('number', 'like', "MH-{$year}-%")->orderByDesc('number')->value('number');
            $seq = $last ? ((int) substr($last, 8)) + 1 : 1;
            $inv->update([
                'number' => sprintf('MH-%s-%04d', $year, $seq),
                'status' => 'verstuurd',
                'sent_at' => now(),
                'due_on' => now()->addDays(config('meldhoornaar.seller.payment_days'))->toDateString(),
            ]);
        });

        $path = 'facturen/meldhoornaar/' . $inv->number . '.pdf';
        Storage::disk('local')->put($path, $this->pdf($inv)->output());
        $inv->update(['pdf_path' => $path]);

        $to = $inv->bestrijder->invoice_email ?: $inv->bestrijder->notify_email;
        if ($to) {
            Notification::route('mail', $to)->notify(new LeadInvoiceSent($inv));
        }
        return $inv;
    }

    public function pdf(LeadInvoice $inv)
    {
        $inv->loadMissing(['bestrijder', 'leads.nest']);
        return Pdf::loadView('pdf.lead-invoice', ['invoice' => $inv, 'seller' => config('meldhoornaar.seller')])->setPaper('a4');
    }
}
