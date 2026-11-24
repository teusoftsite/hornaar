<?php

namespace App\Console\Commands;

use App\Models\Report;
use App\Support\NestStatus;
use Illuminate\Console\Command;

/** AVG: e-mailadressen van melders wissen 90 dagen nadat het nest is afgesloten. */
class PurgePersonalData extends Command
{
    protected $signature = 'privacy:opschonen';

    protected $description = 'Wis e-mailadressen van melders 90 dagen na afsluiten van het nest';

    public function handle(): int
    {
        $closed = [NestStatus::AFGEWEZEN, NestStatus::SAMENGEVOEGD, NestStatus::BESTREDEN, NestStatus::GEFACTUREERD];
        $n = Report::whereNotNull('reporter_email')
            ->whereHas('nest', fn ($q) => $q->whereIn('status', $closed)->where('updated_at', '<', now()->subDays(config('meldhoornaar.purge_reporter_email_days'))))
            ->update(['reporter_email' => null, 'reporter_name' => null, 'reporter_phone' => null]);
        $l = \App\Models\Lead::whereIn('status', ['afgerond', 'geen_bestrijder', 'geannuleerd'])
            ->where('updated_at', '<', now()->subDays(config('meldhoornaar.purge_reporter_email_days')))
            ->where('name', '!=', 'gewist')
            ->update(['name' => 'gewist', 'phone' => '-', 'email' => null]);
        $this->info("{$n} e-mailadres(sen) en {$l} aanvraag/aanvragen gewist.");
        return self::SUCCESS;
    }
}
