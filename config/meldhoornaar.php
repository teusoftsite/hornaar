<?php

return [
    // Contactformulier /zakelijk en systeemmeldingen.
    'contact_to' => env('MELDHOORNAAR_CONTACT_TO', 'info@meldhoornaar.nl'),
    // Google Analytics 4 meet-ID (G-...). Leeg = geen analytics, geen cookiebanner.
    'ga_id' => env('GA_MEASUREMENT_ID'),

    // Dagen na afhandeling waarna e-mailadressen van melders worden gewist (AVG).
    'purge_reporter_email_days' => 90,
];
