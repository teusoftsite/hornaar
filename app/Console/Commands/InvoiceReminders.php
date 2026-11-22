<?php

namespace App\Console\Commands;

use App\Services\InvoiceService;
use Illuminate\Console\Command;

class InvoiceReminders extends Command
{
    protected $signature = 'facturen:herinneringen';

    protected $description = 'Markeer verlopen facturen als te laat en stuur herinneringen na 7 en 14 dagen';

    public function handle(InvoiceService $service): int
    {
        $this->info($service->processReminders(today()) . ' herinnering(en) verstuurd.');
        return self::SUCCESS;
    }
}
