<?php

namespace App\Http\Controllers\Bestrijder;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Services\NestService;
use App\Support\NestStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    public static function own(Assignment $a): Assignment
    {
        $user = auth()->user();
        abort_unless($a->contract->bestrijder_id === $user->organisation_id, 404);
        abort_unless($user->isBestrijderEigenaar() || $a->assigned_user_id === null || $a->assigned_user_id === $user->id, 404);
        return $a;
    }

    public function index(Request $request)
    {
        $org = $this->org();
        $user = auth()->user();
        $assignments = Assignment::forBestrijder($org->id)
            ->whereIn('status', ['open', 'ingepland'])
            ->when(! $user->isBestrijderEigenaar(), fn ($q) => $q->where(fn ($w) => $w->whereNull('assigned_user_id')->orWhere('assigned_user_id', $user->id)))
            ->with('nest.reports', 'contract.gemeente', 'assignee')
            ->orderByRaw("case when status = 'open' then 0 else 1 end")->orderBy('planned_on')->orderBy('route_order')
            ->get();
        $done = Assignment::forBestrijder($org->id)->where('status', 'afgerond')->with('nest', 'workOrders')->latest('updated_at')->limit(20)->get();
        $team = $org->users()->where('active', true)->whereIn('role', ['bestrijder_eigenaar', 'bestrijder_medewerker'])->orderBy('name')->get();

        return view('bestrijder.opdrachten', compact('assignments', 'done', 'team', 'org'));
    }

    public function plan(Request $request, Assignment $assignment, NestService $nests)
    {
        self::own($assignment);
        abort_unless(in_array($assignment->status, ['open', 'ingepland'], true), 422);
        $user = auth()->user();
        $data = $request->validate([
            'planned_on' => ['required', 'date', 'after_or_equal:today'],
            'assigned_user_id' => ['nullable', 'integer'],
        ], ['planned_on.after_or_equal' => 'Kies vandaag of later.']);

        $assignee = $user->isBestrijderEigenaar() && ! empty($data['assigned_user_id'])
            ? $user->organisation->users()->findOrFail($data['assigned_user_id'])->id
            : $user->id;

        return $this->attempt(function () use ($assignment, $data, $assignee, $nests, $user) {
            DB::transaction(function () use ($assignment, $data, $assignee, $nests, $user) {
                $assignment->update(['planned_on' => $data['planned_on'], 'assigned_user_id' => $assignee, 'status' => 'ingepland', 'route_order' => null]);
                if ($assignment->nest->status !== NestStatus::INGEPLAND) {
                    $nests->transition($assignment->nest, NestStatus::INGEPLAND, $user, 'Gepland op ' . \Carbon\Carbon::parse($data['planned_on'])->format('d-m-Y'));
                }
            });
        }, 'Ingepland.');
    }
}
