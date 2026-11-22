<?php

namespace App\Http\Controllers\Bestrijder;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\WorkOrder;
use App\Services\InvoiceService;
use App\Services\NestService;
use App\Services\PhotoStore;
use App\Support\NestStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkOrderController extends Controller
{
    public function show(Assignment $assignment)
    {
        AssignmentController::own($assignment)->load('nest.reports', 'contract.gemeente', 'workOrders');
        return view('bestrijder.werkbon', ['assignment' => $assignment, 'nest' => $assignment->nest]);
    }

    /** Ook doel van de offline-wachtrij: idempotent op client_uuid. */
    public function store(Request $request, Assignment $assignment, NestService $nests, InvoiceService $invoices, PhotoStore $photos)
    {
        AssignmentController::own($assignment);
        $data = $request->validate([
            'client_uuid' => ['required', 'uuid'],
            'result' => ['required', 'in:' . implode(',', array_keys(WorkOrder::RESULTS))],
            'started_at' => ['nullable', 'date'],
            'finished_at' => ['nullable', 'date'],
            'agent' => ['nullable', 'string', 'max:190'],
            'admission_number' => ['nullable', 'string', 'max:30'],
            'quantity' => ['nullable', 'string', 'max:50'],
            'surcharges' => ['nullable', 'array'],
            'surcharges.*' => ['in:hoogwerker,urgent'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photo_before' => ['nullable', 'image', 'max:12288'],
            'photo_after' => ['nullable', 'image', 'max:12288'],
        ]);

        if ($existing = WorkOrder::where('client_uuid', $data['client_uuid'])->first()) {
            return $this->done($request, $existing, 200);
        }
        if ($assignment->status === 'afgerond' || $assignment->status === 'geannuleerd') {
            return $request->expectsJson()
                ? response()->json(['message' => 'Deze opdracht is al afgerond of geannuleerd.'], 409)
                : back()->withErrors(['result' => 'Deze opdracht is al afgerond of geannuleerd.']);
        }

        $before = $request->file('photo_before') ? $photos->store($request->file('photo_before'), 'werkbonnen') : null;
        $after = $request->file('photo_after') ? $photos->store($request->file('photo_after'), 'werkbonnen') : null;
        $user = auth()->user();

        $wo = DB::transaction(function () use ($assignment, $data, $before, $after, $user, $nests, $invoices) {
            $wo = WorkOrder::create([
                'assignment_id' => $assignment->id,
                'client_uuid' => $data['client_uuid'],
                'user_id' => $user->id,
                'photo_before' => $before,
                'photo_after' => $after,
                'started_at' => $data['started_at'] ?? null,
                'finished_at' => $data['finished_at'] ?? now(),
                'agent' => $data['agent'] ?? null,
                'admission_number' => $data['admission_number'] ?? null,
                'quantity' => $data['quantity'] ?? null,
                'surcharges' => array_values($data['surcharges'] ?? []),
                'result' => $data['result'],
                'notes' => $data['notes'] ?? null,
            ]);
            $assignment->update(['status' => 'afgerond']);

            $nest = $assignment->nest;
            if ($nest->status === NestStatus::OPDRACHT || $nest->status === NestStatus::GEEN_TOEGANG) {
                $nests->transition($nest, NestStatus::INGEPLAND, $user, null, false);
            }
            $to = match ($data['result']) {
                'bestreden' => NestStatus::BESTREDEN,
                'geen_toegang' => NestStatus::GEEN_TOEGANG,
                'geen_nest' => NestStatus::AFGEWEZEN,
            };
            $nests->transition($nest, $to, $user, 'Werkbon: ' . WorkOrder::RESULTS[$data['result']] . (! empty($data['notes']) ? ' – ' . $data['notes'] : ''));

            $invoices->lineForWorkOrder($wo);
            return $wo;
        });

        return $this->done($request, $wo, 201);
    }

    private function done(Request $request, WorkOrder $wo, int $code)
    {
        return $request->expectsJson()
            ? response()->json(['id' => $wo->id, 'client_uuid' => $wo->client_uuid], $code)
            : redirect()->route('bestrijder.opdrachten')->with('status', 'Werkbon opgeslagen.');
    }
}
