<?php

return [
    'mimes' => 'File extension is not allowed',
    'type_exists' => 'The :attribute does not exist in the DB.',
    'category_exists' => 'The :attribute does not exist in the DB.',
    'brand_exists' => 'The :attribute does not exist in the DB.',
    'manufacturer_exists' => 'The :attribute does not exist in the DB.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'required' => 'The :attribute field is required.',
    'custom' => [
        'phone_number' => [
            'region' => 'The phone number is not the correct format for :region',
        ],
    ],
    'integer' => 'The :attribute needs to be an integer.',
    'array' => 'The :attribute needs to be an array.',
    'price_format' => 'The format for :attribute is incorrect. Starting price needs to be lower than end price. Formats supported are: [10 TO 100], [10.05 TO 100], [10.05 TO 100.05], [10 TO 100.05], [10], [10.05]'
];
