<?php

use App\Http\Controllers\Beheer;
use App\Http\Controllers\Bestrijder;
use App\Http\Controllers\Gemeente;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TwoFactorSetupController;
use Illuminate\Support\Facades\Route;

// Publiek
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/melden', [ReportController::class, 'create'])->name('melden');
Route::post('/melden', [ReportController::class, 'store'])->middleware('throttle:reports')->name('melden.store');
Route::get('/m/{token}', [ReportController::class, 'status'])->name('melding.status');
Route::get('/zakelijk', [PageController::class, 'zakelijk'])->name('zakelijk');
Route::post('/zakelijk/contact', [PageController::class, 'contact'])->middleware('throttle:5,60')->name('zakelijk.contact');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/foto/{path}', PhotoController::class)->where('path', '.*')->middleware('signed')->name('foto');

Route::middleware(['auth', '2fa'])->group(function () {
    Route::get('/home', fn () => redirect(auth()->user()->homeRoute()))->name('home.redirect');
    Route::get('/tweestaps', [TwoFactorSetupController::class, 'show'])->name('tweestaps');

    // Gemeente
    Route::middleware('role:gemeente')->prefix('gemeente')->name('gemeente.')->group(function () {
        Route::get('/', [Gemeente\NestController::class, 'index'])->name('index');
        Route::get('/nesten/{nest}', [Gemeente\NestController::class, 'show'])->name('nesten.show');
        Route::post('/nesten/{nest}/goedkeuren', [Gemeente\NestController::class, 'approve'])->name('nesten.approve');
        Route::post('/nesten/{nest}/afwijzen', [Gemeente\NestController::class, 'reject'])->name('nesten.reject');
        Route::post('/nesten/{nest}/samenvoegen', [Gemeente\NestController::class, 'merge'])->name('nesten.merge');
        Route::post('/nesten/{nest}/opdracht', [Gemeente\NestController::class, 'assign'])->name('nesten.assign');
        Route::get('/rapport', [Gemeente\RapportController::class, 'index'])->name('rapport');
        Route::get('/rapport/export', [Gemeente\RapportController::class, 'export'])->name('rapport.export');
        Route::get('/facturen/{invoice}/pdf', [Gemeente\RapportController::class, 'invoicePdf'])->name('facturen.pdf');
    });

    // Bestrijder
    Route::middleware('role:bestrijder')->prefix('bestrijder')->name('bestrijder.')->group(function () {
        Route::get('/', [Bestrijder\DashboardController::class, 'index'])->name('index');
        Route::get('/opdrachten', [Bestrijder\AssignmentController::class, 'index'])->name('opdrachten');
        Route::post('/opdrachten/{assignment}/plannen', [Bestrijder\AssignmentController::class, 'plan'])->name('opdrachten.plan');
        Route::get('/route', [Bestrijder\RouteController::class, 'index'])->name('route');
        Route::post('/route/optimaliseren', [Bestrijder\RouteController::class, 'optimize'])->name('route.optimize');
        Route::get('/werkbon/{assignment}', [Bestrijder\WorkOrderController::class, 'show'])->name('werkbon');
        Route::post('/werkbon/{assignment}', [Bestrijder\WorkOrderController::class, 'store'])->name('werkbon.store');

        Route::middleware('role:bestrijder_eigenaar')->group(function () {
            Route::get('/facturen', [Bestrijder\InvoiceController::class, 'index'])->name('facturen');
            Route::get('/facturen/{invoice}', [Bestrijder\InvoiceController::class, 'show'])->name('facturen.show');
            Route::get('/facturen/{invoice}/pdf', [Bestrijder\InvoiceController::class, 'pdf'])->name('facturen.pdf');
            Route::post('/facturen/regel/{line}', [Bestrijder\InvoiceController::class, 'updateLine'])->name('facturen.line');
            Route::post('/facturen/{invoice}/versturen', [Bestrijder\InvoiceController::class, 'send'])->name('facturen.send');
            Route::post('/facturen/{invoice}/betaald', [Bestrijder\InvoiceController::class, 'paid'])->name('facturen.paid');
            Route::post('/facturen/{invoice}/crediteren', [Bestrijder\InvoiceController::class, 'credit'])->name('facturen.credit');
            Route::get('/instellingen', [Bestrijder\SettingsController::class, 'index'])->name('instellingen');
            Route::post('/instellingen', [Bestrijder\SettingsController::class, 'update'])->name('instellingen.update');
            Route::post('/instellingen/medewerkers', [Bestrijder\SettingsController::class, 'addUser'])->name('instellingen.users');
            Route::post('/instellingen/medewerkers/{user}/actief', [Bestrijder\SettingsController::class, 'toggleUser'])->name('instellingen.users.toggle');
        });
    });

    // Beheer
    Route::middleware('role:beheerder')->prefix('beheer')->name('beheer.')->group(function () {
        Route::get('/', [Beheer\BeheerController::class, 'index'])->name('index');
        Route::post('/organisaties', [Beheer\BeheerController::class, 'storeOrganisation'])->name('organisaties.store');
        Route::get('/organisaties/{organisation}', [Beheer\BeheerController::class, 'showOrganisation'])->name('organisaties.show');
        Route::post('/organisaties/{organisation}', [Beheer\BeheerController::class, 'updateOrganisation'])->name('organisaties.update');
        Route::post('/organisaties/{organisation}/gebruikers', [Beheer\BeheerController::class, 'storeUser'])->name('gebruikers.store');
        Route::post('/gebruikers/{user}/actief', [Beheer\BeheerController::class, 'toggleUser'])->name('gebruikers.toggle');
        Route::post('/contracten', [Beheer\BeheerController::class, 'storeContract'])->name('contracten.store');
        Route::post('/nesten/{nest}/gemeente', [Beheer\BeheerController::class, 'routeNest'])->name('nesten.route');
    });
});
