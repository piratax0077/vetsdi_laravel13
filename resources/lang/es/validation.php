<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mensajes de validación
    |--------------------------------------------------------------------------
    |
    | Mensajes que muestra el validador cuando una regla no se cumple.
    | Algunas reglas tienen variantes según el tipo de dato (número, archivo,
    | texto o arreglo). Deben estar todas para que nunca salga uno en inglés.
    |
    */

    'accepted' => 'El campo :attribute debe ser aceptado.',
    'accepted_if' => 'El campo :attribute debe ser aceptado cuando :other es :value.',
    'active_url' => 'El campo :attribute no es una URL válida.',
    'after' => 'El campo :attribute debe ser una fecha posterior a :date.',
    'after_or_equal' => 'El campo :attribute debe ser una fecha posterior o igual a :date.',
    'alpha' => 'El campo :attribute solo puede contener letras.',
    'alpha_dash' => 'El campo :attribute solo puede contener letras, números, guiones y guiones bajos.',
    'alpha_num' => 'El campo :attribute solo puede contener letras y números.',
    'any_of' => 'El campo :attribute no es válido.',
    'array' => 'El campo :attribute debe ser una matriz.',
    'ascii' => 'El campo :attribute solo puede contener letras, números y símbolos simples.',
    'base64' => 'El campo :attribute debe ser una cadena codificada en base64.',
    'before' => 'El campo :attribute debe ser una fecha anterior a :date.',
    'before_or_equal' => 'El campo :attribute debe ser una fecha anterior o igual a :date.',
    'between' => [
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'file' => 'El campo :attribute debe estar entre :min y :max kilobytes.',
        'string' => 'El campo :attribute debe tener entre :min y :max caracteres.',
        'array' => 'El campo :attribute debe tener entre :min y :max elementos.',
    ],
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'can' => 'El campo :attribute contiene un valor no autorizado.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'contains' => 'Al campo :attribute le falta un valor obligatorio.',
    'current_password' => 'La contraseña es incorrecta.',
    'date' => 'El campo :attribute no es una fecha válida.',
    'date_equals' => 'El campo :attribute debe ser una fecha igual a :date.',
    'date_format' => 'El campo :attribute no coincide con el formato :format.',
    'decimal' => 'El campo :attribute debe tener :decimal decimales.',
    'declined' => 'El campo :attribute debe ser rechazado.',
    'declined_if' => 'El campo :attribute debe ser rechazado cuando :other es :value.',
    'different' => 'El campo :attribute y :other deben ser diferentes.',
    'digits' => 'El campo :attribute debe tener :digits dígitos.',
    'digits_between' => 'El campo :attribute debe tener entre :min y :max dígitos.',
    'dimensions' => 'El campo :attribute tiene dimensiones de imagen inválidas.',
    'distinct' => 'El campo :attribute tiene un valor duplicado.',
    'doesnt_contain' => 'El campo :attribute no debe contener ninguno de los siguientes: :values.',
    'doesnt_end_with' => 'El campo :attribute no debe terminar con ninguno de los siguientes: :values.',
    'doesnt_start_with' => 'El campo :attribute no debe comenzar con ninguno de los siguientes: :values.',
    'email' => 'El campo :attribute debe ser una dirección de correo electrónico válida.',
    'encoding' => 'El campo :attribute debe estar codificado en :encoding.',
    'ends_with' => 'El campo :attribute debe terminar con uno de los siguientes: :values.',
    'enum' => 'El :attribute seleccionado es inválido.',
    'exists' => 'El :attribute seleccionado es inválido.',
    'extensions' => 'El campo :attribute debe tener una de las siguientes extensiones: :values.',
    'file' => 'El campo :attribute debe ser un archivo.',
    'filled' => 'El campo :attribute debe tener un valor.',
    'gt' => [
        'numeric' => 'El campo :attribute debe ser mayor que :value.',
        'file' => 'El campo :attribute debe ser mayor que :value kilobytes.',
        'string' => 'El campo :attribute debe ser mayor que :value caracteres.',
        'array' => 'El campo :attribute debe tener más de :value elementos.',
    ],
    'gte' => [
        'numeric' => 'El campo :attribute debe ser mayor que o igual a :value.',
        'file' => 'El campo :attribute debe ser mayor que o igual a :value kilobytes.',
        'string' => 'El campo :attribute debe ser mayor que o igual a :value caracteres.',
        'array' => 'El campo :attribute debe tener :value elementos o más.',
    ],
    'hex_color' => 'El campo :attribute debe ser un color hexadecimal válido.',
    'image' => 'El campo :attribute debe ser una imagen.',
    'in' => 'El :attribute seleccionado es inválido.',
    'in_array' => 'El campo :attribute no existe en :other.',
    'in_array_keys' => 'El campo :attribute debe contener al menos una de las siguientes claves: :values.',
    'integer' => 'El campo :attribute debe ser un entero.',
    'ip' => 'El campo :attribute debe ser una dirección IP válida.',
    'ipv4' => 'El campo :attribute debe ser una dirección IPv4 válida.',
    'ipv6' => 'El campo :attribute debe ser una dirección IPv6 válida.',
    'json' => 'El campo :attribute debe ser una cadena JSON válida.',
    'list' => 'El campo :attribute debe ser una lista.',
    'lowercase' => 'El campo :attribute debe estar en minúsculas.',
    'lt' => [
        'numeric' => 'El campo :attribute debe ser menor que :value.',
        'file' => 'El campo :attribute debe ser menor que :value kilobytes.',
        'string' => 'El campo :attribute debe ser menor que :value caracteres.',
        'array' => 'El campo :attribute debe tener menos de :value elementos.',
    ],
    'lte' => [
        'numeric' => 'El campo :attribute debe ser menor que o igual a :value.',
        'file' => 'El campo :attribute debe ser menor que o igual a :value kilobytes.',
        'string' => 'El campo :attribute debe ser menor que o igual a :value caracteres.',
        'array' => 'El campo :attribute no debe tener más de :value elementos.',
    ],
    'mac_address' => 'El campo :attribute debe ser una dirección MAC válida.',
    'max' => [
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'file' => 'El campo :attribute no debe ser mayor que :max kilobytes.',
        'string' => 'El campo :attribute no debe ser mayor que :max caracteres.',
        'array' => 'El campo :attribute no debe tener más de :max elementos.',
    ],
    'max_digits' => 'El campo :attribute no debe tener más de :max dígitos.',
    'mimes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'mimetypes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'min' => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'file' => 'El campo :attribute debe tener al menos :min kilobytes.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
        'array' => 'El campo :attribute debe tener al menos :min elementos.',
    ],
    'min_digits' => 'El campo :attribute debe tener al menos :min dígitos.',
    'missing' => 'El campo :attribute no debe estar presente.',
    'missing_if' => 'El campo :attribute no debe estar presente cuando :other es :value.',
    'missing_unless' => 'El campo :attribute no debe estar presente a menos que :other sea :value.',
    'missing_with' => 'El campo :attribute no debe estar presente cuando :values está presente.',
    'missing_with_all' => 'El campo :attribute no debe estar presente cuando :values están presentes.',
    'multiple_of' => 'El campo :attribute debe ser un múltiplo de :value.',
    'not_in' => 'El :attribute seleccionado es inválido.',
    'not_regex' => 'El formato del campo :attribute es inválido.',
    'numeric' => 'El campo :attribute debe ser un número.',
    'password' => [
        'letters' => 'El campo :attribute debe contener al menos una letra.',
        'mixed' => 'El campo :attribute debe contener al menos una letra mayúscula y una minúscula.',
        'numbers' => 'El campo :attribute debe contener al menos un número.',
        'symbols' => 'El campo :attribute debe contener al menos un símbolo.',
        'uncompromised' => 'La :attribute ingresada apareció en una filtración de datos. Por favor elige otra.',
    ],
    'present' => 'El campo :attribute debe estar presente.',
    'present_if' => 'El campo :attribute debe estar presente cuando :other es :value.',
    'present_unless' => 'El campo :attribute debe estar presente a menos que :other sea :value.',
    'present_with' => 'El campo :attribute debe estar presente cuando :values está presente.',
    'present_with_all' => 'El campo :attribute debe estar presente cuando :values están presentes.',
    'prohibited' => 'El campo :attribute está prohibido.',
    'prohibited_if' => 'El campo :attribute está prohibido cuando :other es :value.',
    'prohibited_if_accepted' => 'El campo :attribute está prohibido cuando :other es aceptado.',
    'prohibited_if_declined' => 'El campo :attribute está prohibido cuando :other es rechazado.',
    'prohibited_unless' => 'El campo :attribute está prohibido a menos que :other esté en :values.',
    'prohibits' => 'El campo :attribute prohíbe que :other esté presente.',
    'regex' => 'El formato del campo :attribute es inválido.',
    'required' => 'El campo :attribute es obligatorio.',
    'required_array_keys' => 'El campo :attribute debe contener entradas para: :values.',
    'required_if' => 'El campo :attribute es obligatorio cuando :other es :value.',
    'required_if_accepted' => 'El campo :attribute es obligatorio cuando :other es aceptado.',
    'required_if_declined' => 'El campo :attribute es obligatorio cuando :other es rechazado.',
    'required_unless' => 'El campo :attribute es obligatorio a menos que :other esté en :values.',
    'required_with' => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_with_all' => 'El campo :attribute es obligatorio cuando :values están presentes.',
    'required_without' => 'El campo :attribute es obligatorio cuando :values no está presente.',
    'required_without_all' => 'El campo :attribute es obligatorio cuando ninguno de :values están presentes.',
    'same' => 'El campo :attribute y :other deben coincidir.',
    'size' => [
        'numeric' => 'El campo :attribute debe ser :size.',
        'file' => 'El campo :attribute debe tener :size kilobytes.',
        'string' => 'El campo :attribute debe tener :size caracteres.',
        'array' => 'El campo :attribute debe contener :size elementos.',
    ],
    'starts_with' => 'El campo :attribute debe comenzar con uno de los siguientes: :values.',
    'string' => 'El campo :attribute debe ser una cadena.',
    'timezone' => 'El campo :attribute debe ser una zona horaria válida.',
    'ulid' => 'El campo :attribute debe ser un ULID válido.',
    'unique' => 'El valor del campo :attribute ya ha sido tomado.',
    'uploaded' => 'El campo :attribute falló al cargar.',
    'uppercase' => 'El campo :attribute debe estar en mayúsculas.',
    'url' => 'El campo :attribute debe ser una URL válida.',
    'uuid' => 'El campo :attribute debe ser un UUID válido.',

    /*
    |--------------------------------------------------------------------------
    | Mensajes personalizados por campo
    |--------------------------------------------------------------------------
    |
    | Para dar un mensaje distinto a un campo y regla puntual, usando la
    | forma "campo.regla".
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nombres de los campos
    |--------------------------------------------------------------------------
    |
    | Reemplazan el nombre interno del campo en los mensajes, para que se lea
    | "correo electrónico" en vez de "email".
    |
    */

    'attributes' => [
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'password_confirmation' => 'confirmación de contraseña',
        'current_password' => 'contraseña actual',
        'name' => 'nombre',
        'rut' => 'RUT',
        'code' => 'código',
        'recovery_code' => 'código de recuperación',
    ],

];
