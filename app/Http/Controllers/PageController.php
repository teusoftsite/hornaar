<?php

namespace App\Http\Controllers;

use App\Models\Nest;
use App\Support\NestStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    public function home()
    {
        $stats = cache()->remember('home.stats', 600, fn () => [
            'gemeld' => Nest::where('status', '!=', NestStatus::SAMENGEVOEGD)->whereYear('created_at', now()->year)->count(),
            'bestreden' => Nest::whereIn('status', [NestStatus::BESTREDEN, NestStatus::GEFACTUREERD])->whereYear('created_at', now()->year)->count(),
        ]);
        return view('public.home', compact('stats'));
    }

    public function zakelijk() { return view('public.zakelijk'); }

    public function contact(Request $request)
    {
        if ($request->filled('website')) {
            return back()->with('status', 'Bedankt! We nemen binnen twee werkdagen contact op.'); // honeypot
        }
        $d = $request->validate([
            'naam' => ['required', 'string', 'max:120'],
            'organisatie' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'telefoon' => ['nullable', 'string', 'max:40'],
            'type' => ['nullable', 'in:Gemeente,Bestrijder,Anders'],
        ]);
        $text = "Contactaanvraag via MeldHoornaar.nl\n\nNaam: {$d['naam']}\nOrganisatie: {$d['organisatie']}\nType: " . ($d['type'] ?? '-')
            . "\nE-mail: {$d['email']}\nTelefoon: " . ($d['telefoon'] ?? '-') . "\nTijd: " . now()->format('d-m-Y H:i') . "\n";
        Mail::raw($text, fn ($m) => $m->to(config('meldhoornaar.contact_to'))->replyTo($d['email'])->subject('Contact: ' . $d['organisatie']));
        return redirect(route('zakelijk') . '#contact')->with('status', 'Bedankt! We nemen binnen twee werkdagen contact op.');
    }

    public function privacy() { return view('public.privacy'); }
}
