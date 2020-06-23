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

    'accepted'             => 'O campo :attribute deve ser aceito.',
    'active_url'           => 'O campo :attribute não tem uma URL válida',
    'after'                => 'O campo :attribute deve ser uma data após :date',
    'after_or_equal'       => 'O campo :attribute deve ser uma data após ou igual a :date',
    'alpha'                => 'O campo :attribute só pode conter letras.',
    'alpha_dash'           => 'O campo :attribute só pode conter letras, números e traços.',
    'alpha_num'            => 'O campo :attribute só pode conter letras e números.',
    'array'                => 'O campo :attribute deve ser um array.',
    'before'               => 'O campo :attribute deve ser uma data antes de :date.',
    'before_or_equal'      => 'O campo :attribute deve ser uma data antes de ou igual a :date.',
    'between'              => [
        'numeric' => 'O campo :attribute deve estar entre :min e :max.',
        'file'    => 'O campo :attribute deve ter entre :min e :max kilobytes.',
        'string'  => 'O campo :attribute deve ter entre :min e :max characters.',
        'array'   => 'O campo :attribute deve ter entre :min e :max items.',
    ],
    'boolean'              => 'O campo :attribute deve ser verdadeiro ou falso.',
    'confirmed'            => 'O campo de confirmação de :attribute não está correto.',
    'date'                 => 'O campo :attribute não é uma data válida',
    'date_format'          => 'O campo :attribute não está no formato :format.',
    'different'            => 'O campo :attribute e :other devem ser diferentes.',
    'digits'               => 'O campo :attribute deve ter :digits digitos.',
    'digits_between'       => 'O campo :attribute deve ter entre :min e :max digitos.',
    'dimensions'           => 'A imagem no campo :attribute tem um tamanho inválido.',
    'distinct'             => 'O campo :attribute possui valor duplicado.',
    'email'                => 'O campo :attribute deve ser um email válido.',
    'exists'               => 'O :attribute selecionado é invalido.',
    'file'                 => 'O campo :attribute deve ser um arquivo.',
    'filled'               => 'O campo :attribute deve ter um valor.',
    'image'                => 'O campo :attribute deve ser uma imagem.',
    'in'                   => 'O :attribute selecionado é invalido.',
    'in_array'             => 'O campo :attribute não existe em :other.',
    'integer'              => 'O campo :attribute deve ser um número inteiro.',
    'ip'                   => 'O campo :attribute deve conter um IP válido.',
    'ipv4'                 => 'O campo :attribute deve conter um IPv4 válido.',
    'ipv6'                 => 'O campo :attribute deve conter um IPv6 válido..',
    'json'                 => 'O campo :attribute deve ser um JSON válido.',
    'max'                  => [
        'numeric' => 'O campo :attribute não pode ser maior que :max.',
        'file'    => 'O campo :attribute não pode ser maior que :max kilobytes.',
        'string'  => 'O campo :attribute não pode ser maior que :max caracteres.',
        'array'   => 'O campo :attribute não pode ter mais que :max items.',
    ],
    'mimes'                => 'O campo :attribute deve ser um arquivo do tipo: :values.',
    'mimetypes'            => 'O campo :attribute deve ser um arquivo do tipo: :values.',
    'min'                  => [
        'numeric' => 'O campo :attribute deve ser no mínimo :min.',
        'file'    => 'O campo :attribute deve ter no mínimo :min kilobytes.',
        'string'  => 'O campo :attribute deve ter no mínimo :min characters.',
        'array'   => 'O campo :attribute must have at least :min items.',
    ],
    'not_in'               => 'O :attribute selecionado é invalido.',
    'numeric'              => 'O campo :attribute deve ser um número.',
    'present'              => 'O campo :attribute não pode estar vazio.',
    'regex'                => 'O campo :attribute está num formato inválido.',
    'required'             => 'O campo :attribute é obrigatório.',
    'required_if'          => 'O campo :attribute é obrigatório quando :other é :value.',
    'required_unless'      => 'O campo :attribute é obrigatório a não ser que :other esteja em :values.',
    'required_with'        => 'O campo :attribute é obrigatório quando :values está preenchido.',
    'required_with_all'    => 'O campo :attribute é obrigatório quando :values está preenchido.',
    'required_without'     => 'O campo :attribute é obrigatório quando :values não está preenchido.',
    'required_without_all' => 'O campo :attribute é obrigatório quando nenhum destes está preenchido: :values.',
    'same'                 => 'Os campos :attribute e :other devem ser equivalentes.',
    'size'                 => [
        'numeric' => 'O campo :attribute deve ter o tamanho :size.',
        'file'    => 'O campo :attribute deve ter :size kilobytes.',
        'string'  => 'O campo :attribute deve ter :size caracteres.',
        'array'   => 'O campo :attribute deve conter :size items.',
    ],
    'string'               => 'O campo :attribute deve ser um texto.',
    'timezone'             => 'O campo :attribute deve ser um fuso horário válido.',
    'unique'               => 'O campo :attribute possui um valor que já foi usado.',
    'uploaded'             => 'O campo :attribute falhou ao fazer o upload.',
    'url'                  => 'O campo :attribute está num formato inválido.',

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