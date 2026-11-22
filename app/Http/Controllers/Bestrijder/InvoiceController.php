<?php

namespace App\Http\Controllers\Bestrijder;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Services\InvoiceService;
use App\Support\Money;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $service) {}

    private function own(Invoice $invoice): Invoice
    {
        abort_unless($invoice->bestrijder_id === $this->org()->id, 404);
        return $invoice;
    }

    public function index()
    {
        $org = $this->org();
        $invoices = Invoice::where('bestrijder_id', $org->id)->with('gemeente')->orderByRaw("case status when 'concept' then 0 when 'te_laat' then 1 when 'verstuurd' then 2 else 3 end")->latest()->paginate(30);
        $unbilled = InvoiceLine::whereNull('invoice_id')->whereHas('contract', fn ($q) => $q->where('bestrijder_id', $org->id))->sum('amount_cents');
        return view('bestrijder.facturen', compact('invoices', 'unbilled', 'org'));
    }

    public function show(Invoice $invoice)
    {
        $this->own($invoice)->load('lines', 'gemeente', 'contract');
        return view('bestrijder.factuur', compact('invoice'));
    }

    public function pdf(Invoice $invoice)
    {
        $this->own($invoice);
        return $this->service->pdf($invoice)->stream('factuur-' . ($invoice->number ?? 'concept') . '.pdf');
    }

    public function updateLine(Request $request, InvoiceLine $line)
    {
        abort_unless($line->invoice && $line->invoice->bestrijder_id === $this->org()->id, 404);
        $data = $request->validate([
            'amount' => ['required', 'string'],
            'description' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', 'max:255'],
        ], ['reason.required' => 'Geef een reden voor de aanpassing.']);
        return $this->attempt(fn () => $this->service->updateLine($line, Money::parse($data['amount']), $data['description'], auth()->user(), $data['reason']), 'Regel aangepast.');
    }

    public function send(Invoice $invoice)
    {
        $this->own($invoice);
        return $this->attempt(fn () => $this->service->send($invoice, auth()->user()), 'Factuur verstuurd.');
    }

    public function paid(Invoice $invoice)
    {
        $this->own($invoice);
        abort_unless(in_array($invoice->status, ['verstuurd', 'te_laat'], true), 422);
        $this->service->markPaid($invoice, auth()->user());
        return back()->with('status', 'Gemarkeerd als betaald.');
    }

    public function credit(Invoice $invoice)
    {
        $this->own($invoice);
        $credit = null;
        $resp = $this->attempt(function () use ($invoice, &$credit) { $credit = $this->service->credit($invoice, auth()->user()); }, 'Creditfactuur verstuurd.');
        return $credit ? redirect()->route('bestrijder.facturen.show', $credit)->with('status', 'Creditfactuur ' . $credit->number . ' verstuurd.') : $resp;
    }
}
