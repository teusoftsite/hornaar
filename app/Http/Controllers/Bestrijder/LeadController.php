<?php

namespace App\Http\Controllers\Bestrijder;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(private LeadService $leads) {}

    public function index()
    {
        $org = $this->org();
        return view('bestrijder.leads', [
            'org' => $org,
            'offered' => Lead::where('status', 'aangeboden')->where('offered_to_id', $org->id)->with('nest.reports')->latest('offered_at')->get(),
            'mine' => Lead::where('bestrijder_id', $org->id)->with('nest.reports')->latest('accepted_at')->limit(50)->get(),
        ]);
    }

    public function accept(Lead $lead)
    {
        return $this->attempt(fn () => $this->leads->accept($lead, auth()->user()), 'Aanvraag geaccepteerd. Neem contact op met de inwoner.');
    }

    public function decline(Lead $lead)
    {
        return $this->attempt(fn () => $this->leads->decline($lead, auth()->user()), 'Aanvraag geweigerd.');
    }

    public function complete(Request $request, Lead $lead)
    {
        $result = $request->validate(['result' => ['required', 'in:bestreden,geen_nest']])['result'];
        return $this->attempt(fn () => $this->leads->complete($lead, auth()->user(), $result), 'Afgerond.');
    }
}
