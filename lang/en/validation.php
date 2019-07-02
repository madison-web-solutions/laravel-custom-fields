<?php

return [
    'default' => [
        'required' => 'This field is required',
        'null-required' => 'This field must by empty',
        'invalid' => 'Invalid value',
        'min' => 'The minimum value is :min',
        'max' => 'The maximum value is :max',
    ],
    'fields' => [
        'choice' => [
            'invalid' => 'Please select from one of the options',
            'not-in-choices' => 'Please select from one of the options',
        ],
        'integer' => [
            'invalid' => 'The value must be a valid integer',
        ],
        'currency' => [
            'invalid' => 'The value must be a valid currency amount',
        ],
        'compound' => [
        ],
        'date' => [
            'invalid' => 'The value must be a valid date',
            'min' => 'The minimum date is :min',
            'max' => 'The maximum date is :max',
        ],
        'link' => [
        ],
        'markdown' => [
            'min' => 'Minimum length is :min characters',
            'max' => 'Maximum length is :max characters',
        ],
        'media-id' => [
            'missing' => 'Media not found with id :id',
            'missing-category' => 'Media of type :category not found with id :id',
        ],
        'model-id' => [
            'missing' => ':model not found with id :id',
        ],
        'number' => [
            'invalid' => 'The value must be a valid number',
        ],
        'options' => [
        ],
        'repeater' => [
            'invalid' => 'The value must be a valid number',
            'min' => 'The minimum number of items is :min',
            'max' => 'The maximum number of items is :max',
        ],
        'switch' => [
        ],
        'textarea' => [
            'min' => 'Minimum length is :min characters',
            'max' => 'Maximum length is :max characters',
        ],
        'text' => [
            'min' => 'Minimum length is :min characters',
            'max' => 'Maximum length is :max characters',
            'invalid-url' => 'The value must be a valid url',
            'invalid-uuid' => 'The value must be a valid uuid',
            'invalid-email' => 'The value must be a valid email',
            'invalid-ip' => 'The value must be a valid ip address',
            'invalid-ipv4' => 'The value must be a valid (v4) ip address',
            'invalid-ipv6' => 'The value must be a valid (v6) ip address',
        ],
        'time' => [
            'invalid' => 'The value must be a valid time',
            'min' => 'The minimum time is :min',
            'max' => 'The maximum time is :max',
        ],
        'timestamp' => [
            'invalid' => 'The value must be a valid datetime',
            'min' => 'The minimum datetime is :min',
            'max' => 'The maximum datetime is :max',
        ],
        'toggle' => [
        ],
    ],
];
