<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\StatusEvent;
use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\InvoiceReminder;
use App\Notifications\InvoiceSent;
use App\Support\Money;
use App\Support\NestStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    public function __construct(private NestService $nests) {}

    /** Werkbon → factuurregel (of geen regel). */
    public function lineForWorkOrder(WorkOrder $wo): ?InvoiceLine
    {
        $assignment = $wo->assignment()->with(['contract', 'nest'])->first();
        $c = $assignment->contract;
        $nest = $assignment->nest;
        $surcharges = $wo->surcharges ?? [];

        [$amount, $what] = match ($wo->result) {
            'bestreden' => [
                $c->standard_rate_cents
                    + (in_array('hoogwerker', $surcharges, true) ? $c->surcharge_height_cents : 0)
                    + (in_array('urgent', $surcharges, true) ? $c->surcharge_urgent_cents : 0),
                'Bestrijding nest',
            ],
            'geen_toegang' => [$c->callout_cents, 'Voorrijkosten (geen toegang)'],
            default => [null, null],
        };

        if ($amount === null) {
            return null;
        }

        $extra = array_map(fn ($s) => $s === 'hoogwerker' ? 'hoogwerker' : 'spoed', array_intersect($surcharges, ['hoogwerker', 'urgent']));
        $desc = $what . ' #' . $nest->id . ($nest->address ? ' – ' . $nest->address : '')
            . ' (' . ($wo->finished_at ?? $wo->created_at)->format('d-m-Y') . ')'
            . ($extra && $wo->result === 'bestreden' ? ', toeslag ' . implode(' + ', $extra) : '');

        return InvoiceLine::create([
            'contract_id' => $c->id,
            'work_order_id' => $wo->id,
            'description' => $desc,
            'amount_cents' => $amount,
        ]);
    }

    /** Bundelt alle open regels t/m het einde van de vorige maand tot conceptfacturen (één per contract). */
    public function generateMonthly(CarbonInterface $runDate): array
    {
        $periodEnd = $runDate->copy()->startOfMonth()->subSecond();
        $period = $periodEnd->format('Y-m');
        $created = [];

        $open = InvoiceLine::query()
            ->whereNull('invoice_id')
            ->where('created_at', '<=', $periodEnd)
            ->get()
            ->groupBy('contract_id');

        foreach ($open as $contractId => $lines) {
            $contract = Contract::with('bestrijder')->find($contractId);
            $created[] = DB::transaction(function () use ($contract, $lines, $period) {
                $invoice = Invoice::create([
                    'bestrijder_id' => $contract->bestrijder_id,
                    'gemeente_id' => $contract->gemeente_id,
                    'contract_id' => $contract->id,
                    'period' => $period,
                    'status' => 'concept',
                    'vat_rate' => $contract->bestrijder->vat_rate,
                ]);
                InvoiceLine::whereIn('id', $lines->pluck('id'))->update(['invoice_id' => $invoice->id]);
                return $this->recalc($invoice);
            });
        }

        return $created;
    }

    public function recalc(Invoice $invoice): Invoice
    {
        $sub = (int) $invoice->lines()->sum('amount_cents');
        $vat = (int) round($sub * $invoice->vat_rate / 100);
        $invoice->update(['subtotal_cents' => $sub, 'vat_cents' => $vat, 'total_cents' => $sub + $vat]);
        return $invoice;
    }

    public function updateLine(InvoiceLine $line, int $amount, string $description, User $by, string $reason): void
    {
        $invoice = $line->invoice;
        if (! $invoice || ! $invoice->isEditable()) {
            throw new \DomainException('Een verstuurde factuur kan niet meer worden gewijzigd. Maak een creditfactuur.');
        }
        $old = Money::format($line->amount_cents);
        $line->update(['amount_cents' => $amount, 'description' => $description]);
        StatusEvent::create([
            'invoice_id' => $invoice->id, 'from' => 'concept', 'to' => 'concept', 'user_id' => $by->id,
            'note' => "Regel #{$line->id} aangepast van {$old} naar " . Money::format($amount) . ": {$reason}",
        ]);
        $this->recalc($invoice);
    }

    public function nextNumber(int $bestrijderId, int $year): string
    {
        $last = Invoice::where('bestrijder_id', $bestrijderId)
            ->where('number', 'like', $year . '-%')
            ->orderByDesc('number')->value('number');
        $seq = $last ? ((int) substr($last, 5)) + 1 : 1;
        return sprintf('%d-%04d', $year, $seq);
    }

    public function send(Invoice $invoice, ?User $by = null): Invoice
    {
        if (! $invoice->isEditable()) {
            throw new \DomainException('Deze factuur is al verstuurd.');
        }

        DB::transaction(function () use ($invoice, $by) {
            $invoice->refresh();
            $invoice->update([
                'number' => $this->nextNumber($invoice->bestrijder_id, (int) now()->format('Y')),
                'status' => 'verstuurd',
                'sent_at' => now(),
                'due_on' => now()->addDays($invoice->contract->payment_days)->toDateString(),
            ]);
            StatusEvent::create(['invoice_id' => $invoice->id, 'from' => 'concept', 'to' => 'verstuurd', 'user_id' => $by?->id]);

            foreach ($invoice->lines()->with('workOrder.assignment.nest')->get() as $line) {
                $nest = $line->workOrder?->assignment?->nest;
                if ($nest && $nest->status === NestStatus::BESTREDEN) {
                    $this->nests->transition($nest, NestStatus::GEFACTUREERD, $by, 'Factuur ' . $invoice->number, false);
                }
            }
        });

        $path = $this->storePdf($invoice);
        $invoice->update(['pdf_path' => $path]);

        if ($invoice->gemeente->invoice_email) {
            Notification::route('mail', $invoice->gemeente->invoice_email)->notify(new InvoiceSent($invoice));
        }

        return $invoice;
    }

    public function credit(Invoice $original, User $by): Invoice
    {
        if ($original->isEditable() || $original->credits_invoice_id) {
            throw new \DomainException('Alleen een verstuurde factuur kan gecrediteerd worden.');
        }
        $credit = DB::transaction(function () use ($original) {
            $credit = Invoice::create([
                'bestrijder_id' => $original->bestrijder_id,
                'gemeente_id' => $original->gemeente_id,
                'contract_id' => $original->contract_id,
                'period' => $original->period,
                'status' => 'concept',
                'credits_invoice_id' => $original->id,
                'vat_rate' => $original->vat_rate,
            ]);
            foreach ($original->lines as $l) {
                InvoiceLine::create([
                    'invoice_id' => $credit->id,
                    'contract_id' => $l->contract_id,
                    'description' => 'Credit: ' . $l->description,
                    'amount_cents' => -$l->amount_cents,
                ]);
            }
            return $this->recalc($credit);
        });

        return $this->send($credit, $by);
    }

    public function markPaid(Invoice $invoice, User $by): void
    {
        $from = $invoice->status;
        $invoice->update(['status' => 'betaald', 'paid_at' => now()]);
        StatusEvent::create(['invoice_id' => $invoice->id, 'from' => $from, 'to' => 'betaald', 'user_id' => $by->id]);
    }

    /** Te laat markeren + herinnering na 7 en 14 dagen over de vervaldatum. */
    public function processReminders(CarbonInterface $today): int
    {
        $sent = 0;
        $open = Invoice::whereIn('status', ['verstuurd', 'te_laat'])->whereNull('credits_invoice_id')
            ->whereNotNull('due_on')->whereDate('due_on', '<', $today)->get();

        foreach ($open as $inv) {
            if ($inv->status === 'verstuurd') {
                $inv->update(['status' => 'te_laat']);
                StatusEvent::create(['invoice_id' => $inv->id, 'from' => 'verstuurd', 'to' => 'te_laat']);
            }
            $daysLate = $inv->due_on->diffInDays($today);
            $due = $daysLate >= 14 ? 2 : ($daysLate >= 7 ? 1 : 0);
            if ($due > $inv->reminders_sent && $inv->gemeente->invoice_email) {
                Notification::route('mail', $inv->gemeente->invoice_email)->notify(new InvoiceReminder($inv, $due));
                $inv->update(['reminders_sent' => $due]);
                $sent++;
            }
        }
        return $sent;
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->loadMissing(['lines', 'bestrijder', 'gemeente', 'contract']);
        return Pdf::loadView('pdf.invoice', ['invoice' => $invoice])->setPaper('a4');
    }

    public function storePdf(Invoice $invoice): string
    {
        $path = 'facturen/' . $invoice->bestrijder_id . '/' . ($invoice->number ?? 'concept-' . $invoice->id) . '.pdf';
        Storage::disk('local')->put($path, $this->pdf($invoice)->output());
        return $path;
    }
}
