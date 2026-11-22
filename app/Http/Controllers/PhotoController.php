<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

/** Foto's staan buiten de webroot en zijn alleen via een ondertekende URL (1 uur geldig) op te vragen. */
class PhotoController extends Controller
{
    public function __invoke(string $path)
    {
        abort_if(str_contains($path, '..'), 404);
        abort_unless(Storage::disk('local')->exists($path), 404);
        return response()->file(Storage::disk('local')->path($path), ['Cache-Control' => 'private, max-age=3600']);
    }
}
