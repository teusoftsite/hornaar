<?php

namespace App\Http\Controllers\Beheer;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadInvoice;
use App\Services\LeadInvoiceService;

class LeadAdminController extends Controller
{
    public function __construct(private LeadInvoiceService $invoices) {}

    public function index()
    {
        return view('beheer.leads', [
            'leads' => Lead::with('nest', 'offeredTo', 'bestrijder')->latest()->paginate(50),
            'invoices' => LeadInvoice::with('bestrijder')->latest()->limit(50)->get(),
            'counts' => Lead::selectRaw('status, count(*) n')->groupBy('status')->pluck('n', 'status'),
        ]);
    }

    public function send(LeadInvoice $leadInvoice)
    {
        return $this->attempt(fn () => $this->invoices->send($leadInvoice), 'Factuur verstuurd.');
    }

    public function paid(LeadInvoice $leadInvoice)
    {
        abort_unless($leadInvoice->status === 'verstuurd', 422);
        $leadInvoice->update(['status' => 'betaald', 'paid_at' => now()]);
        return back()->with('status', 'Gemarkeerd als betaald.');
    }

    public function pdf(LeadInvoice $leadInvoice)
    {
        return $this->invoices->pdf($leadInvoice)->stream('factuur-' . ($leadInvoice->number ?? 'concept') . '.pdf');
    }
}
