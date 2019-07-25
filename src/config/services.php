<?php

return [
    'stripe' => [
        'server_api_key' => env('STRIPE_SECRET'),
        'client_api_key' => env('STRIPE_CLIENT'),
    ],
];
