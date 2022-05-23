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

    'between' => [
        'numeric' => ':attribute deve estar entre :min e :max.',
        'file' => ':attribute deve estar entre :min e :max kilobytes.',
        'string' => ':attribute deve estar entre :min e :max caracteres.',
        'array' => ':attribute deve ter entre :min e :max itens.',
    ],
    'boolean' => ':attribute precisa ser verdadeiro ou falso.',
    'confirmed' => 'A confirmação de :attribute não confere.',
    'current_password' => 'A senha está incorreta.',
    'date' => ':attribute não é uma data válida.',
    'email' => ':attribute precisa ser um endereço de e-mail válido.',
    'exists' => 'O valor de :attribute é inválido.',
    'integer' => ':attribute precisa ser um número inteiro.',
    'max' => [
        'numeric' => ':attribute não pode ser maior que :max.',
        'file' => ':attribute não pode ser maior que :max kilobytes.',
        'string' => ':attribute não pode ter mais que :max caracteres.',
        'array' => ':attribute não pode ter mais que :max itens.',
    ],
    'min' => [
        'numeric' => ':attribute precisa ser pelo menos :min.',
        'file' => ':attribute precisa ter pelo menos :min kilobytes.',
        'string' => ':attribute precisa ter pelo menos :min caracteres.',
        'array' => ':attribute precisa ter pelo menos :min itens.',
    ],
    'numeric' => ':attribute precisa ser um número.',
    'password' => 'A senha está incorreta.',
    'required' => ':attribute é obrigatório.',
    'required_with' => ':attribute é requerido quando :values for preenchido.',
    'size' => [
        'numeric' => ':attribute precisa ser :size.',
        'file' => ':attribute precisa ter :size kilobytes.',
        'string' => ':attribute deve ter :size caracteres.',
        'array' => ':attribute precisa conter :size itens.',
    ],
    'unique' => ':attribute já está em uso.',

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
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'password' => 'senha',
        'group' => 'grupo',
        'category_id' => 'categoria',
        'description' => 'descrição',
        'value' => 'valor',
        'paid_at' => 'data',
        'name' => 'nome',
    ],

];
