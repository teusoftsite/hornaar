<?php

return [
    // Contactformulier /zakelijk en systeemmeldingen.
    'contact_to' => env('MELDHOORNAAR_CONTACT_TO', 'info@meldhoornaar.nl'),
    // Dagen na afhandeling waarna e-mailadressen van melders worden gewist (AVG).
    'purge_reporter_email_days' => 90,
];
