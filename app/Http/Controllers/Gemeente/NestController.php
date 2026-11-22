<?php

namespace App\Http\Controllers\Gemeente;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Contract;
use App\Models\Nest;
use App\Notifications\AssignmentCreated;
use App\Services\NestService;
use App\Support\NestStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class NestController extends Controller
{
    public function __construct(private NestService $nests) {}

    private function own(Nest $nest): Nest
    {
        // Organisatie-scheiding: een gemeente ziet alleen haar eigen nesten (404, geen 403: bestaan niet lekken).
        abort_unless($nest->gemeente_id === $this->org()->id, 404);
        return $nest;
    }

    public function index(Request $request)
    {
        $org = $this->org();
        $status = $request->query('status', 'open');
        $q = Nest::where('gemeente_id', $org->id)->with('reports', 'activeAssignment.contract.bestrijder')->latest();
        match ($status) {
            'open' => $q->whereIn('status', NestStatus::OPEN),
            'alle' => $q->where('status', '!=', NestStatus::SAMENGEVOEGD),
            default => $q->where('status', $status),
        };
        $nests = $q->paginate(50)->withQueryString();

        $counts = Nest::where('gemeente_id', $org->id)->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        return view('gemeente.index', compact('nests', 'counts', 'status', 'org'));
    }

    public function show(Nest $nest)
    {
        $this->own($nest)->load('reports', 'events.user', 'assignments.contract.bestrijder', 'assignments.workOrders', 'mergedInto');
        return view('gemeente.show', [
            'nest' => $nest,
            'duplicates' => $nest->status === NestStatus::GEMELD ? $this->nests->duplicateCandidates($nest) : collect(),
            'contracts' => Contract::active()->where('gemeente_id', $nest->gemeente_id)->with('bestrijder')->get(),
        ]);
    }

    public function approve(Request $request, Nest $nest)
    {
        $this->own($nest);
        $data = $request->validate(['urgent' => ['nullable', 'boolean'], 'note' => ['nullable', 'string', 'max:500']]);
        return $this->attempt(function () use ($nest, $data) {
            $nest->update(['urgent' => (bool) ($data['urgent'] ?? false)]);
            $this->nests->transition($nest, NestStatus::GOEDGEKEURD, auth()->user(), $data['note'] ?? null);
        }, 'Melding goedgekeurd.');
    }

    public function reject(Request $request, Nest $nest)
    {
        $this->own($nest);
        $data = $request->validate(['note' => ['required', 'string', 'max:500']], ['note.required' => 'Geef een reden op.']);
        return $this->attempt(fn () => $this->nests->transition($nest, NestStatus::AFGEWEZEN, auth()->user(), $data['note']), 'Melding afgewezen.');
    }

    public function merge(Request $request, Nest $nest)
    {
        $this->own($nest);
        $target = $this->own(Nest::findOrFail($request->validate(['target_id' => ['required', 'integer']])['target_id']));
        $this->nests->merge($nest, $target, auth()->user());
        return redirect()->route('gemeente.nesten.show', $target)->with('status', 'Melding samengevoegd met nest #' . $target->id . '.');
    }

    public function assign(Request $request, Nest $nest)
    {
        $this->own($nest);
        $contract = Contract::active()->where('gemeente_id', $nest->gemeente_id)->findOrFail($request->validate(['contract_id' => ['required', 'integer']])['contract_id']);

        return $this->attempt(function () use ($nest, $contract) {
            $assignment = DB::transaction(function () use ($nest, $contract) {
                $nest->assignments()->whereIn('status', ['open', 'ingepland'])->update(['status' => 'geannuleerd']);
                $a = Assignment::create(['nest_id' => $nest->id, 'contract_id' => $contract->id, 'status' => 'open']);
                $this->nests->transition($nest, NestStatus::OPDRACHT, auth()->user(), 'Opdracht aan ' . $contract->bestrijder->name);
                return $a;
            });
            if ($contract->bestrijder->notify_email ?: $contract->bestrijder->invoice_email) {
                Notification::route('mail', $contract->bestrijder->notify_email ?: $contract->bestrijder->invoice_email)
                    ->notify(new AssignmentCreated($assignment->load('nest', 'contract.gemeente')));
            }
        }, 'Opdracht gegeven aan ' . $contract->bestrijder->name . '.');
    }
}
