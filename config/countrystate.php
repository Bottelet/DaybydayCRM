<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Set language
    |--------------------------------------------------------------------------
    |
    | Limited translations of countries are supported.
    | By default, English is used.
    | To use another language, use the three character
    | ISO 639-3 code.
    | Example: 'spa'
    |
    */
    'language' => 'eng',

    /*
    |--------------------------------------------------------------------------
    | Limit countries to load automatically
    |--------------------------------------------------------------------------
    |
    | By default, all countries will be loaded.
    | This is quite slow, so you can define an array
    | of country codes you want to load automatically.
    | Example: ['US', 'CA']
    |
    */
    'limitCountries' => [],

    /*
    |--------------------------------------------------------------------------
    | Load states automatically for countries
    |--------------------------------------------------------------------------
    |
    | You can define an array of countries who's
    | states will automatically be loaded.
    |
    */
    'preloadCountryStates' => ['CA', 'MX', 'US'],
];
