<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Publieke meld-API (gebruikt door het meldformulier). Max 5 per IP per uur.
Route::post('/reports', [ReportController::class, 'store'])->middleware('throttle:reports')->name('api.reports.store');
