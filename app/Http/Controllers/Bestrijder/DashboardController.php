<?php

namespace App\Http\Controllers\Bestrijder;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Invoice;

class DashboardController extends Controller
{
    public function index()
    {
        $org = $this->org();
        $user = auth()->user();
        $base = Assignment::forBestrijder($org->id)->when(! $user->isBestrijderEigenaar(), fn ($q) => $q->where('assigned_user_id', $user->id));

        return view('bestrijder.index', [
            'org' => $org,
            'open' => (clone $base)->where('status', 'open')->count(),
            'today' => (clone $base)->where('status', 'ingepland')->whereDate('planned_on', today())->with('nest')->orderBy('route_order')->get(),
            'planned' => (clone $base)->where('status', 'ingepland')->count(),
            'concepts' => $user->isBestrijderEigenaar() ? Invoice::where('bestrijder_id', $org->id)->where('status', 'concept')->count() : 0,
            'late' => $user->isBestrijderEigenaar() ? Invoice::where('bestrijder_id', $org->id)->where('status', 'te_laat')->count() : 0,
        ]);
    }
}
