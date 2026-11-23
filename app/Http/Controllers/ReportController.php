<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\ReportService;
use App\Services\Turnstile;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function create()
    {
        return view('public.melden', ['turnstile' => config('services.turnstile.site_key')]);
    }

    public function store(Request $request, ReportService $service, Turnstile $turnstile)
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:50.7,53.6'],
            'lng' => ['required', 'numeric', 'between:3.3,7.3'],
            'photo' => ['nullable', 'image', 'max:12288'],
            'location_type' => ['nullable', 'in:boom,gebouw,struik,grond'],
            'height_m' => ['nullable', 'numeric', 'between:0,40'],
            'note' => ['nullable', 'string', 'max:1000'],
            'email' => ['nullable', 'email', 'max:190'],
        ], [
            'lat.required' => 'Kies de plek van het nest op de kaart.',
            'lat.between' => 'Deze plek ligt niet in Nederland.',
            'lng.between' => 'Deze plek ligt niet in Nederland.',
            'photo.image' => 'De foto moet een afbeelding zijn.',
            'photo.max' => 'De foto is te groot (max 12 MB).',
        ]);

        if (! $turnstile->verify($request->input('cf-turnstile-response'), $request->ip())) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Controle mislukt, probeer het opnieuw.'], 422)
                : back()->withErrors(['lat' => 'Controle mislukt, probeer het opnieuw.'])->withInput();
        }

        $report = $service->create($data, $request->file('photo'), $request->ip());
        $url = route('melding.status', ['token' => $report->token, 'nieuw' => 1]);

        return $request->expectsJson()
            ? response()->json(['id' => $report->nest_id, 'status_url' => $url, 'gemeente' => $report->nest->gemeente?->name], 201)
            : redirect($url)->with('status', 'Bedankt! Je melding is ontvangen.');
    }

    public function status(string $token)
    {
        $report = Report::where('token', $token)->with('nest.events', 'nest.gemeente', 'nest.mergedInto')->firstOrFail();
        $nest = $report->nest;
        return view('public.status', compact('report', 'nest'));
    }
}
