<?php

namespace App\Http\Controllers;

use App\Models\Organisation;

abstract class Controller
{
    protected function org(): Organisation
    {
        $org = auth()->user()?->organisation;
        abort_unless($org, 403);
        return $org;
    }

    /** Doorgaan met een DomainException als nette foutmelding. */
    protected function attempt(callable $fn, string $ok)
    {
        try {
            $fn();
        } catch (\DomainException $e) {
            return back()->withErrors(['action' => $e->getMessage()]);
        }
        return back()->with('status', $ok);
    }
}
