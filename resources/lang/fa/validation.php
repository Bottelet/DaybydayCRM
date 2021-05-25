<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'این :attribute باید پذیرفته شود.',
    'active_url'           => 'این :attribute URL معتبر نیست.',
    'after'                => 'این :attribute باید تاریخ پس از :date باشد.',
    'after_or_equal'       => 'این :attribute باید تاریخی پس از یا برابر با :date باشد.',
    'alpha'                => 'این :attribute may فقط می تواند حاوی حروف باشد.',
    'alpha_dash'           => 'این :attribute فقط می تواند حاوی حروف ، اعداد و خط تیره باشد.',
    'alpha_num'            => 'این :attribute فقط می تواند حاوی حروف و اعداد باشد.',
    'array'                => 'این :attribute باید یک آرایه باشد.',
    'before'               => 'این :attribute باید تاریخ قبل از :date باشد.',
    'before_or_equal'      => ':attribute باید تاریخ قبل یا برابر با :date باشد.',
    'between'              => [
        'numeric' => 'این :attribute باید بین :min و :max باشد.',
        'file'    => 'این :attribute باید بین کیلوبایت های :min و :max باشد.',
        'string'  => 'این :attribute باید بین نویسه های :min و :max باشد.',
        'array'   => 'این :attribute باید بین موارد :min و :max باشد.',
    ],
    'boolean'              => 'این :attribute باید درست یا نادرست باشد.',
    'confirmed'            => 'The :attribute confirmation does not match.',
    'date'                 => 'The :attribute is not a valid date.',
    'date_format'          => 'The :attribute does not match the format :format.',
    'different'            => 'The :attribute and :other must be different.',
    'digits'               => 'The :attribute must be :digits digits.',
    'digits_between'       => 'The :attribute must be between :min and :max digits.',
    'dimensions'           => 'The :attribute has invalid image dimensions.',
    'distinct'             => 'The :attribute field has a duplicate value.',
    'email'                => 'این :attribute باید یک آدرس ایمیل معتبر باشد.',
    'exists'               => ':attribute انتخابی نامعتبر است.',
    'file'                 => ':attribute باید یک پرونده باشد.',
    'filled'               => 'این :attribute باید دارای یک مقدار باشد.',
    'image'                => ':attribute باید یک تصویر باشد.',
    'in'                   => 'The selected :attribute is invalid.',
    'in_array'             => 'The :attribute field does not exist in :other.',
    'integer'              => 'The :attribute must be an integer.',
    'ip'                   => 'The :attribute must be a valid IP address.',
    'ipv4'                 => 'The :attribute must be a valid IPv4 address.',
    'ipv6'                 => 'The :attribute must be a valid IPv6 address.',
    'json'                 => 'The :attribute must be a valid JSON string.',
    'max'                  => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file'    => 'The :attribute may not be greater than :max kilobytes.',
        'string'  => 'The :attribute may not be greater than :max characters.',
        'array'   => 'The :attribute may not have more than :max items.',
    ],
    'mimes'                => 'The :attribute must be a file of type: :values.',
    'mimetypes'            => 'The :attribute must be a file of type: :values.',
    'min'                  => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'not_in'               => 'The selected :attribute is invalid.',
    'numeric'              => 'The :attribute must be a number.',
    'present'              => 'The :attribute field must be present.',
    'regex'                => 'The :attribute format is invalid.',
    'required'             => 'The :attribute field is required.',
    'required_if'          => 'The :attribute field is required when :other is :value.',
    'required_unless'      => 'The :attribute field is required unless :other is in :values.',
    'required_with'        => 'The :attribute field is required when :values is present.',
    'required_with_all'    => 'The :attribute field is required when :values is present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same'                 => 'The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'string'               => ':attribute باید یک رشته باشد.',
    'timezone'             => 'The :attribute must be a valid zone.',
    'unique'               => 'این :attribute قبلاً گرفته شده است.',
    'uploaded'             => 'این :attribute بارگیری نشد.',
    'url'                  => 'قالب :attribute نامعتبر است.',

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
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
