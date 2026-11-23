<?php

namespace App\Console\Commands;

use App\Services\LeadService;
use Illuminate\Console\Command;

class ExpireLeads extends Command
{
    protected $signature = 'leads:doorzetten';

    protected $description = 'Zet particuliere aanvragen waarop niet op tijd is gereageerd door naar de volgende bestrijder';

    public function handle(LeadService $leads): int
    {
        $this->info($leads->expireOverdue() . ' aanvraag/aanvragen doorgezet.');
        return self::SUCCESS;
    }
}
