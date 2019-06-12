<?php

return [
    'choice' => [
        'required' => 'This field is required',
        'invalid' => 'Please select from one of the options',
        'not-in-choices' => 'Please select from one of the options',
    ],
    'integer' => [
        'required' => 'This field is required',
        'invalid' => 'The value must be a valid integer',
        'min' => 'The minimum value is :min',
        'max' => 'The maximum value is :max',
    ],
    'currency' => [
        'required' => 'This field is required',
        'invalid' => 'The value must be a valid currency amount',
        'min' => 'The minimum value is :min',
        'max' => 'The maximum value is :max',
    ],
    'compound' => [
        'required' => 'This field is required',
        'invalid' => 'Invalid value',
    ],
    'date' => [
        'required' => 'This field is required',
        'invalid' => 'The value must be a valid date',
        'min' => 'The minimum date is :min',
        'max' => 'The maximum date is :max',
    ],
    'link' => [
        'required' => 'This field is required',
        'invalid' => 'Invalid value',
    ],
    'markdown' => [
        'required' => 'This field is required',
        'invalid' => 'Invalid value',
        'min' => 'Minimum length is :min characters',
        'max' => 'Maximum length is :max characters',
    ],
    'media-id' => [
        'required' => 'This field is required',
        'invalid' => 'Invalid value',
        'missing' => 'Media not found with id :id',
        'missing-category' => 'Media of type :category not found with id :id',
    ],
    'model-id' => [
        'required' => 'This field is required',
        'invalid' => 'Invalid value',
        'missing' => ':model not found with id :id',
    ],
    'number' => [
        'required' => 'This field is required',
        'invalid' => 'The value must be a valid number',
        'min' => 'The minimum value is :min',
        'max' => 'The maximum value is :max',
    ],
    'options' => [
        'required' => 'This field is required',
        'invalid' => 'Invalid value',
    ],
    'repeater' => [
        'required' => 'This field is required',
        'invalid' => 'The value must be a valid number',
        'min' => 'The minimum number of items is :min',
        'max' => 'The maximum number of items is :max',
    ],
    'switch' => [
        'required' => 'This field is required',
        'invalid' => 'Invalid value',
    ],
    'textarea' => [
        'required' => 'This field is required',
        'invalid' => 'Invalid value',
        'min' => 'Minimum length is :min characters',
        'max' => 'Maximum length is :max characters',
    ],
    'text' => [
        'required' => 'This field is required',
        'invalid' => 'Invalid value',
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
        'required' => 'This field is required',
        'invalid' => 'The value must be a valid time',
        'min' => 'The minimum time is :min',
        'max' => 'The maximum time is :max',
    ],
    'timestamp' => [
        'required' => 'This field is required',
        'invalid' => 'The value must be a valid datetime',
        'min' => 'The minimum datetime is :min',
        'max' => 'The maximum datetime is :max',
    ],
    'toggle' => [
        'required' => 'This field is required',
        'invalid' => 'Invalid value',
    ],
];
