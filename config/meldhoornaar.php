<?php

return [
    // Contactformulier /zakelijk en systeemmeldingen.
    'contact_to' => env('MELDHOORNAAR_CONTACT_TO', 'info@meldhoornaar.nl'),
    // Google Analytics 4 meet-ID (G-...). Leeg = geen analytics, geen cookiebanner.
    'ga_id' => env('GA_MEASUREMENT_ID'),

    // Particuliere leads
    'lead_fee_cents' => (int) env('MELDHOORNAAR_LEAD_FEE_CENTS', 1000),
    'lead_response_hours' => (int) env('MELDHOORNAAR_LEAD_RESPONSE_HOURS', 4),

    // Gegevens van MeldHoornaar op facturen aan bestrijders
    'seller' => [
        'name' => env('MH_SELLER_NAME', 'EasySoft Software BV'),
        'trade_name' => 'MeldHoornaar.nl',
        'address' => env('MH_SELLER_ADDRESS', 'Allegro 83, 2925 BB Krimpen aan den IJssel'),
        'kvk' => env('MH_SELLER_KVK'),
        'vat_number' => env('MH_SELLER_VAT'),
        'iban' => env('MH_SELLER_IBAN'),
        'email' => env('MH_SELLER_EMAIL', 'info@meldhoornaar.nl'),
        'vat_rate' => 21,
        'payment_days' => 14,
    ],

    // Dagen na afhandeling waarna e-mailadressen van melders worden gewist (AVG).
    'purge_reporter_email_days' => 90,
];
