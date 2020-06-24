<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN', null),
        'secret' => env('MAILGUN_SECRET', null),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.eu.mailgun.net'),
    ],

    'mandrill' => [
        'secret' => env('MANDRILL_SECRET'),
    ],

    'ses' => [
        'key'    => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],
    'dinero' => [
        'secret' => env('DINERO_SECRET', null),
        'client' => env('DINERO_CLIENT_ID', null),
    ],
    'stripe' => [
        'model'  => App\Models\Tenant::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
    'dropbox' => [
        'client_id' => env('DROPBOX_CLIENT_ID', null),
        'client_secret' => env('DROPBOX_CLIENT_SECRET', null),
    ],
    'google-drive' => [
        'client_id' => env('GOOGLE_DRIVE_CLIENT_ID', null),
        'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET', null),
    ],
    'elasticsearch' => [
        'enabled' => env('ELASTICSEARCH_ENABLED', false),
    ],
];
