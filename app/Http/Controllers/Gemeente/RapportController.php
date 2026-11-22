<?php

namespace App\Http\Controllers\Gemeente;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Nest;
use App\Services\InvoiceService;
use App\Support\NestStatus;
use Illuminate\Http\Request;

class RapportController extends Controller
{
    private function range(Request $r): array
    {
        $year = (int) $r->query('jaar', now()->year);
        return [$year, now()->setDate($year, 1, 1)->startOfDay(), now()->setDate($year, 12, 31)->endOfDay()];
    }

    public function index(Request $request)
    {
        $org = $this->org();
        [$year, $from, $to] = $this->range($request);
        $nests = Nest::where('gemeente_id', $org->id)->whereBetween('created_at', [$from, $to])->where('status', '!=', NestStatus::SAMENGEVOEGD);

        $byStatus = (clone $nests)->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');
        $byMonth = (clone $nests)->get(['created_at'])->groupBy(fn ($n) => (int) $n->created_at->format('n'))->map->count();

        $invoices = Invoice::where('gemeente_id', $org->id)->where('status', '!=', 'concept')->where('period', 'like', $year . '-%')->with('bestrijder')->orderByDesc('sent_at')->get();
        $cost = $invoices->sum('total_cents');
        $cleared = ($byStatus[NestStatus::BESTREDEN] ?? 0) + ($byStatus[NestStatus::GEFACTUREERD] ?? 0);

        return view('gemeente.rapport', compact('org', 'year', 'byStatus', 'byMonth', 'invoices', 'cost', 'cleared'));
    }

    public function export(Request $request)
    {
        $org = $this->org();
        [$year, $from, $to] = $this->range($request);
        $rows = Nest::where('gemeente_id', $org->id)->whereBetween('created_at', [$from, $to])->with('reports')->orderBy('id')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['nest', 'gemeld_op', 'status', 'adres', 'lat', 'lng', 'locatie', 'hoogte_m', 'meldingen'], ';');
            foreach ($rows as $n) {
                fputcsv($out, [$n->id, $n->created_at->format('d-m-Y H:i'), NestStatus::label($n->status), $n->address, $n->lat, $n->lng, $n->location_type, $n->height_m, $n->reports->count()], ';');
            }
            fclose($out);
        }, "hoornaar-{$org->cbs_code}-{$year}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function invoicePdf(Invoice $invoice, InvoiceService $service)
    {
        abort_unless($invoice->gemeente_id === $this->org()->id && $invoice->status !== 'concept', 404);
        return $service->pdf($invoice)->download('factuur-' . $invoice->number . '.pdf');
    }
}
