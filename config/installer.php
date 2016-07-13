<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Last published version
    |--------------------------------------------------------------------------
    |
    | This is where you can specify the last version of your application
    | This is used to determine if the application requires an update
    | The current running version is stored in framework/installed
    |
    */
    'last_version' => '1.0',
    'upgrade' => [
        'migrations' => '',
        'seeds' => [],
    ],
    /*
    |--------------------------------------------------------------------------
    | Server Requirements
    |--------------------------------------------------------------------------
    |
    | This is the default Laravel server requirements, you can add as many
    | as your application require, we check if the extension is enabled
    | by looping through the array and run "extension_loaded" on it.
    |
    */
    'requirements' => [
        'openssl',
        'pdo',
        'mbstring',
        'tokenizer'
    ],
    /*
    |--------------------------------------------------------------------------
    | Folders Permissions
    |--------------------------------------------------------------------------
    |
    | This is the default Laravel folders permissions, if your application
    | requires more permissions just add them to the array list bellow.
    |
    */
    'permissions' => [
        'storage/app/',
        'storage/framework/',
        'storage/logs/',
        'bootstrap/cache/',
        '.env'
    ]
];