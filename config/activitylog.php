<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Also log to Laravel's default log handler
    |--------------------------------------------------------------------------
    |
    | If "alsoLogInDefaultLog" the activity will also be logged in the default
    | Laravel logger handler
    |
    */
    'alsoLogInDefaultLog' => true,

    /*
    |--------------------------------------------------------------------------
    | Max age in months for log records
    |--------------------------------------------------------------------------
    |
    | When running the cleanLog-command all recorder older than the number of months
    | specified here will be deleted
    |
    */
    'deleteRecordsOlderThanMonths' => 2,

    /*
    |--------------------------------------------------------------------------
    | Fallback user id if no user is logged in
    |--------------------------------------------------------------------------
    |
    | If you don't specify a user id when logging some activity and no
    | user is logged in, this id will be used.
    |
    */
    'defaultUserId' => '',

    /*
    |--------------------------------------------------------------------------
    | Handler that is called before logging is done
    |--------------------------------------------------------------------------
    |
    | If you want to disable logging based on some custom conditions, create
    | a handler class that implements the BeforeHandlerInterface and
    | reference it here.
    |
    */
    'beforeHandler' => null,
    
    /*
    |--------------------------------------------------------------------------
    | The class name for the related user model
    |--------------------------------------------------------------------------
    |
    | This can be a class name or null. If null the model will be determined 
    | from Laravel's auth configuration.
    |
    */
    'userModel' => null,
];
