<?php

use App\Models\Tenant;

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN', null),
        'secret'   => env('MAILGUN_SECRET', null),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.eu.mailgun.net'),
    ],

    'mandrill' => [
        'secret' => env('MANDRILL_SECRET'),
    ],

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'dinero' => [
        'secret' => env('DINERO_SECRET', null),
        'client' => env('DINERO_CLIENT_ID', null),
    ],
    'stripe' => [
        'model'  => Tenant::class,
        'key'    => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
    'dropbox' => [
        'client_id'     => env('DROPBOX_CLIENT_ID', null),
        'client_secret' => env('DROPBOX_CLIENT_SECRET', null),
    ],
    'google-drive' => [
        'client_id'     => env('GOOGLE_DRIVE_CLIENT_ID', null),
        'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET', null),
    ],
    'elasticsearch' => [
        'enabled' => env('ELASTICSEARCH_ENABLED', false),
    ],
];
