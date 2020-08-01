<?php

return [
  'accepted' => 'El campo :attribute debe ser aceptado.',
  'active_url' => 'El campo :attribute no es una URL válida.',
  'after' => 'El campo :attribute debe ser una fecha después de :date.',
  'after_or_equal' => 'El campo :attribute debe ser una fecha después o igual a :date.',
  'alpha' => 'El campo :attribute sólo puede contener letras.',
  'alpha_dash' => 'El campo :attribute sólo puede contener letras, números y guiones.',
  'alpha_num' => 'El campo :attribute sólo puede contener letras y números.',
  'array' => 'El campo :attribute debe ser un arreglo.',
  'before' => 'El campo :attribute debe ser una fecha antes de :date.',
  'before_or_equal' => 'El campo :attribute debe ser una fecha antes o igual a :date.',
  'between' => [
    'numeric' => 'El campo :attribute debe estar entre :min - :max.',
    'file' => 'El campo :attribute debe estar entre :min - :max kilobytes.',
    'string' => 'El campo :attribute debe estar entre :min - :max caracteres.',
    'array' => 'El campo :attribute debe tener entre :min y :max elementos.',
  ],
  'boolean' => 'El campo :attribute debe ser verdadero o falso.',
  'confirmed' => 'El campo de confirmación de :attribute no coincide.',
  'date' => 'El campo :attribute no es una fecha válida.',
  'date_format' => 'El campo :attribute no corresponde con el formato :format.',
  'different' => 'Los campos :attribute y :other deben ser diferentes.',
  'digits' => 'El campo :attribute debe ser de :digits dígitos.',
  'digits_between' => 'El campo :attribute debe tener entre :min y :max dígitos.',
  'dimensions' => 'El campo :attribute no tiene una dimensión válida.',
  'distinct' => 'El campo :attribute tiene un valor duplicado.',
  'email' => 'El formato del :attribute es inválido.',
  'exists' => 'El campo :attribute seleccionado es inválido.',
  'file' => 'El campo :attribute debe ser un archivo.',
  'filled' => 'El campo :attribute es requerido.',
  'gt' => [
    'numeric' => 'El campo :attribute debe ser mayor que :value.',
    'file' => 'El campo :attribute debe ser mayor que :value kilobytes.',
    'string' => 'El campo :attribute debe ser mayor que :value caracteres.',
    'array' => 'El campo :attribute puede tener hasta :value elementos.',
  ],
  'gte' => [
    'numeric' => 'El campo :attribute debe ser mayor o igual que :value.',
    'file' => 'El campo :attribute debe ser mayor o igual que :value kilobytes.',
    'string' => 'El campo :attribute debe ser mayor o igual que :value caracteres.',
    'array' => 'El campo :attribute puede tener :value elementos o más.',
  ],
  'image' => 'El campo :attribute debe ser una imagen.',
  'in' => 'El campo :attribute seleccionado es inválido.',
  'in_array' => 'El campo :attribute no existe en :other.',
  'integer' => 'El campo :attribute debe ser un entero.',
  'ip' => 'El campo :attribute debe ser una dirección IP válida.',
  'ipv4' => 'El campo :attribute debe ser una dirección IPv4 válida.',
  'ipv6' => 'El campo :attribute debe ser una dirección IPv6 válida.',
  'json' => 'El campo :attribute debe ser una cadena JSON válida.',
  'lt' => [
    'numeric' => 'El campo :attribute debe ser menor que :max.',
    'file' => 'El campo :attribute debe ser menor que :max kilobytes.',
    'string' => 'El campo :attribute debe ser menor que :max caracteres.',
    'array' => 'El campo :attribute puede tener hasta :max elementos.',
  ],
  'lte' => [
    'numeric' => 'El campo :attribute debe ser menor o igual que :max.',
    'file' => 'El campo :attribute debe ser menor o igual que :max kilobytes.',
    'string' => 'El campo :attribute debe ser menor o igual que :max caracteres.',
    'array' => 'El campo :attribute no puede tener más que :max elementos.',
  ],
  'max' => [
    'numeric' => 'El campo :attribute debe ser menor que :max.',
    'file' => 'El campo :attribute debe ser menor que :max kilobytes.',
    'string' => 'El campo :attribute debe ser menor que :max caracteres.',
    'array' => 'El campo :attribute puede tener hasta :max elementos.',
  ],
  'mimes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
  'mimetypes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
  'min' => [
    'numeric' => 'El campo :attribute debe tener al menos :min.',
    'file' => 'El campo :attribute debe tener al menos :min kilobytes.',
    'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    'array' => 'El campo :attribute debe tener al menos :min elementos.',
  ],
  'not_in' => 'El campo :attribute seleccionado es invalido.',
  'not_regex' => 'El formato del campo :attribute es inválido.',
  'numeric' => 'El campo :attribute debe ser un número.',
  'present' => 'El campo :attribute debe estar presente.',
  'regex' => 'El formato del campo :attribute es inválido.',
  'required' => 'El campo :attribute es requerido.',
  'required_if' => 'El campo :attribute es requerido cuando el campo :other es :value.',
  'required_unless' => 'El campo :attribute es requerido a menos que :other esté presente en :values.',
  'required_with' => 'El campo :attribute es requerido cuando :values está presente.',
  'required_with_all' => 'El campo :attribute es requerido cuando :values está presente.',
  'required_without' => 'El campo :attribute es requerido cuando :values no está presente.',
  'required_without_all' => 'El campo :attribute es requerido cuando ningún :values está presente.',
  'same' => 'El campo :attribute y :other debe coincidir.',
  'size' => [
    'numeric' => 'El campo :attribute debe ser :size.',
    'file' => 'El campo :attribute debe tener :size kilobytes.',
    'string' => 'El campo :attribute debe tener :size caracteres.',
    'array' => 'El campo :attribute debe contener :size elementos.',
  ],
  'starts_with' => 'El :attribute debe empezar con uno de los siguientes valores :values',
  'string' => 'El campo :attribute debe ser una cadena.',
  'timezone' => 'El campo :attribute debe ser una zona válida.',
  'unique' => 'El campo :attribute ya ha sido tomado.',
  'uploaded' => 'El campo :attribute no ha podido ser cargado.',
  'url' => 'El formato de :attribute es inválido.',
  'uuid' => 'El :attribute debe ser un UUID valido.',

    /*
    |--------------------------------------------------------------------------
    | Validación del idioma personalizado
    |--------------------------------------------------------------------------
    |
    |	Aquí puede especificar mensajes de validación personalizados para atributos utilizando el
    | convención "attribute.rule" para nombrar las líneas. Esto hace que sea rápido
    | especifique una línea de idioma personalizada específica para una regla de atributo dada.
    |
    */

  'custom' => [
    'attribute-name' => [
      'rule-name' => 'custom-message',
    ],
  ],

    /*
    |--------------------------------------------------------------------------
    | Atributos de validación personalizados
    |--------------------------------------------------------------------------
    |
          | Las siguientes líneas de idioma se utilizan para intercambiar los marcadores de posición de atributo.
          | con algo más fácil de leer, como la dirección de correo electrónico.
          | de "email". Esto simplemente nos ayuda a hacer los mensajes un poco más limpios.
    |
    */

  'attributes' => [],

];
