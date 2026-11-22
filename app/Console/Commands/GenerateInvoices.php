<?php

namespace App\Console\Commands;

use App\Services\InvoiceService;
use Illuminate\Console\Command;

class GenerateInvoices extends Command
{
    protected $signature = 'facturen:maand {--datum= : Rekendatum (standaard vandaag)}';

    protected $description = 'Bundel openstaande werkbonregels tot conceptfacturen (draait de 1e van de maand om 06:00)';

    public function handle(InvoiceService $service): int
    {
        $date = $this->option('datum') ? now()->parse($this->option('datum')) : now();
        $created = $service->generateMonthly($date);
        $this->info(count($created) . ' conceptfactuur/-facturen aangemaakt.');
        return self::SUCCESS;
    }
}
