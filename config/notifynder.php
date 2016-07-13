<?php

/*
|--------------------------------------------------------------------------
| Notifynder Configuration
|--------------------------------------------------------------------------
*/

return [

    /**
     * If you have a different user model
     * please specific it here, this option is not
     * considerate if using notifynder as polymorphic
     */
    'model' => 'App\User',

    /**
     * Do you want have notifynder that work polymorphically?
     * just swap the value to true and you will able to use it!
     */
    'polymorphic' => false,

    /**
     * If you need to extend the model class of
     * Notifynder you just need to change this line
     * With the path / NameSpace of your model and extend it
     * with Fenos\Notifynder\Models\Notification
     */
    'notification_model' => 'Fenos\Notifynder\Models\Notification',

    /**
     * Coordinating a lots notifications that require extra params
     * might cause to forget and not insert the {extra.*} value needed.
     * This flag allow you to cause an exception to be thrown if you miss
     * to store a extra param that the category will need.
     * NOTE: use only in development.
     * WHEN DISABLED: will just remove the {extra.*} markup from the sentence
     */
    'strict_extra' => false,

    /**
     * If you wish to have the translations in a specific file
     * just require the file on the following option.
     *
     * To get started with the translations just reference a key with
     * the language you wish to translate ex 'it' or 'italian' and pass as
     * value an array with the translations
     */
    'translations'  => [

    ],
];
