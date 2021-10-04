<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    |  following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => ' :attribute অবশ্যই গ্রহণ করতে হবে।',
    'active_url'           => ':attribute একটি বৈধ URL নয়',
    'after'                => ' :attribute পরে একটি তারিখ হতে হবে :date.',
    'after_or_equal'       => ' :attribute এর পরে বা সমান একটি তারিখ হতে হবে :date.',
    'alpha'                => ' :attribute শুধুমাত্র অক্ষর থাকতে পারে।',
    'alpha_dash'           => ' :attribute শুধুমাত্র অক্ষর, সংখ্যা এবং ড্যাশ থাকতে পারে।',
    'alpha_num'            => ' :attribute শুধুমাত্র অক্ষর এবং সংখ্যা থাকতে পারে।',
    'array'                => ' :attribute একটি অ্যারে হতে হবে',
    'before'               => ' :attribute আগে একটি তারিখ হতে হবে :date.',
    'before_or_equal'      => ' :attribute আগে বা সমান একটি তারিখ হতে হবে :date.',
    'between'              => [
        'numeric' => ' :attribute :min and :max মধ্যে হতে হবে .',
        'file'    => ' :attribute :min and :max kilobytes মধ্যে হতে হবে .',
        'string'  => ' :attribute :min and :max characters. মধ্যে হতে হবে ',
        'array'   => ' :attribute :min and :max items মধ্যে থাকা আবশ্যক .',
    ],
    'boolean'              => ' :attribute ইনপুট সত্য বা মিথ্যা হতে হবে।',
    'confirmed'            => ' :attribute নিশ্চিতকরণ মেলে না',
    'date'                 => ' :attribute একটি বৈধ তারিখ নয়',
    'date_format'          => ' :attribute বিন্যাসের সাথে মেলে না :format.',
    'different'            => ' :attribute এবং :other ভিন্ন হতে হবে।',
    'digits'               => ' :attribute অবশ্যই :digits সংখ্যা.',
    'digits_between'       => ' :attribute মধ্যে হতে হবে :min and :max সংখ্যা.',
    'dimensions'           => ' :attribute অবৈধ ছবির মাত্রা আছে',
    'distinct'             => ' :attribute ইনপুট একটি সদৃশ মান আছে।',
    'email'                => ' :attribute একটি বৈধ ইমেইল ঠিকানা আবশ্যক.',
    'exists'               => ' নির্বাচিত :attribute অবৈধ.',
    'file'                 => ' :attribute একটি ফাইল হতে হবে।',
    'filled'               => ' :attribute ইনপুট একটি মান থাকতে হবে।',
    'image'                => ' :attribute একটি ছবি হতে হবে।',
    'in'                   => ' নির্বাচিত :attribute অবৈধ.',
    'in_array'             => ' :attribute ইনপুট মধ্যে নেই :other.',
    'integer'              => ' :attribute একটি পূর্ণসংখ্যা হতে হবে',
    'ip'                   => ' :attribute একটি বৈধ IP ঠিকানা হতে হবে।',
    'ipv4'                 => ' :attribute একটি বৈধ IPv4 ঠিকানা হতে হবে।',
    'ipv6'                 => ' :attribute একটি বৈধ IPv6 ঠিকানা হতে হবে।',
    'json'                 => ' :attribute একটি বৈধ JSON স্ট্রিং হতে হবে।',
    'max'                  => [
        'numeric' => ' :attribute :max এর চেয়ে বড় নাও হতে পারে .',
        'file'    => ' :attribute :max kilobytes এর চেয়ে বড় নাও হতে পারে .',
        'string'  => ' :attribute :max characters এর চেয়ে বড় নাও হতে পারে ',
        'array'   => ' :attribute এর বেশি নাও থাকতে পারে :max items.',
    ],
    'mimes'                => ' :attribute এর একটি ফাইল হতে হবে type: :values.',
    'mimetypes'            => ' :attribute এর একটি ফাইল হতে হবে type: :values.',
    'min'                  => [
        'numeric' => ' :attribute নূন্যতম হতে হবে :min.',
        'file'    => ' :attribute নূন্যতম হতে হবে :min kilobytes.',
        'string'  => ' :attribute নূন্যতম হতে হবে :min characters.',
        'array'   => ' :attribute অন্তত থাকতে হবে :min items.',
    ],
    'not_in'               => 'নির্বাচিত :attribute অবৈধ.',
    'numeric'              => ' :attribute অবশ্যই একটি সংখ্যা হবে.',
    'present'              => ' :attribute ইনপুট উপস্থিত থাকতে হবে।',
    'regex'                => ' :attribute বিন্যাস অবৈধ।',
    'required'             => ' :attribute ইনপুট দরকার.',
    'required_if'          => ' :attribute ইনপুট প্রয়োজন হয় যখন :other হয় :value.',
    'required_unless'      => ' :attribute ইনপুট প্রয়োজন হয় যদি না :other মধ্যে আছে :values.',
    'required_with'        => ' :attribute ইনপুট প্রয়োজন হয় যখন :values উপস্থিত.',
    'required_with_all'    => ' :attribute ইনপুট প্রয়োজন হয় যখন  :values উপস্থিত.',
    'required_without'     => ' :attribute ইনপুট প্রয়োজন হয় যখন  :values উপস্থিত নয়।',
    'required_without_all' => ' :attribute ইনপুট প্রয়োজন হয় যখন  কোনটিই :values উপস্থিত আছেন.',
    'same'                 => ' :attribute এবং :other মেলানো.',
    'size'                 => [
        'numeric' => ' :attribute অবশ্যই :size.',
        'file'    => ' :attribute অবশ্যই :size kilobytes.',
        'string'  => ' :attribute অবশ্যই :size characters.',
        'array'   => ' :attribute অবশ্যই থাকতে হবে :size items.',
    ],
    'string'               => ' :attribute অবশ্যই একটি স্ট্রিং',
    'timezone'             => ' :attribute অবশ্যই একটি বৈধ জোন।',
    'unique'               => ' :attribute আগেই নেয়া হয়েছে.',
    'uploaded'             => ' :attribute আপলোড করতে ব্যর্থ হয়েছে।',
    'url'                  => ' :attribute বিন্যাস অবৈধ।',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    |  following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
