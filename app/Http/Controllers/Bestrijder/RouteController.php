<?php

namespace App\Http\Controllers\Bestrijder;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Services\RouteOptimizer;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    private function stops(Request $request)
    {
        $org = $this->org();
        $user = auth()->user();
        $date = $request->input('datum', today()->toDateString());
        $who = $user->isBestrijderEigenaar() ? (int) $request->input('medewerker', $user->id) : $user->id;

        $stops = Assignment::forBestrijder($org->id)->where('status', 'ingepland')
            ->whereDate('planned_on', $date)->where('assigned_user_id', $who)
            ->with('nest.reports', 'contract.gemeente')
            ->orderByRaw('route_order is null')->orderBy('route_order')->orderBy('id')->get();

        return [$org, $date, $who, $stops];
    }

    public function index(Request $request)
    {
        [$org, $date, $who, $stops] = $this->stops($request);
        $team = $org->users()->where('active', true)->orderBy('name')->get();
        return view('bestrijder.route', compact('org', 'date', 'who', 'stops', 'team'));
    }

    public function optimize(Request $request, RouteOptimizer $optimizer)
    {
        [$org, $date, $who, $stops] = $this->stops($request);
        $result = $optimizer->optimize(
            $stops->map(fn ($a) => ['id' => $a->id, 'lat' => $a->nest->lat, 'lng' => $a->nest->lng])->all(),
            $org->base_lat !== null ? (float) $org->base_lat : null,
            $org->base_lng !== null ? (float) $org->base_lng : null,
        );
        foreach ($result['order'] as $i => $id) {
            Assignment::whereKey($id)->update(['route_order' => $i + 1]);
        }
        return redirect()->route('bestrijder.route', ['datum' => $date, 'medewerker' => $who])
            ->with('status', 'Route geoptimaliseerd: ca. ' . number_format($result['meters'] / 1000, 1, ',', '.') . ' km hemelsbreed.');
    }
}
