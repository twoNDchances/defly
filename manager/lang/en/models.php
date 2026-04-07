<?php

return [
    'commons' => [
        'description' => 'Description',
        'created_by' => 'Created by',
        'created_at' => 'Created at',
        'updated_at' => 'Updated at',
    ],
    'permission' => [
        'name' => 'Permission',
        'fields' => [
            'name' => 'Name',
            'applied_for' => 'Applied for',
            'action' => 'Action',
        ],
    ],
    'policy' => [
        'name' => 'Policy',
        'fields' => [
            'name' => 'Name',
        ],
    ],
    'user' => [
        'name' => 'User',
        'fields' => [
            'name' => 'Name',
            'email' => 'Email',
            'email_verified_at' => 'Email verified at',
            'password' => 'Password',
            'is_verified' => 'Is verified',
            'is_root' => 'Is root',
            'is_activated' => 'Is activated',
        ],
    ],
    'label' => [
        'name' => 'Label',
        'fields' => [
            'name' => 'Name',
            'color' => 'Color',
        ],
    ],
    'wordlist' => [
        'name' => 'Wordlist',
        'fields' => [
            'name' => 'Name',
            'type' => 'Type',
            'word_file' => 'File format',
            'word_json' => 'JSON format',
            'word_count' => 'Word count',
        ],
        'extras' => [
            'type' => [
                'file' => 'File',
                'json' => 'JSON',
            ],
            'word' => 'Word',
        ],
    ],
    'engine' => [
        'name' => 'Engine',
        'fields' => [
            'name' => 'Name',
            'input_datatype' => 'Input datatype',
            'type' => 'Type',
            'configurations' => 'Configurations',
            'output_datatype' => 'Output datatype',
        ],
        'extras' => [
            'datatype' => [
                'array' => 'Array',
                'number' => 'Number',
                'string' => 'String',
            ],
            'type' => [
                'indexOf' => 'Index of',
                'merge' => 'Merge',
                'addition' => 'Addition',
                'subtraction' => 'Subtraction',
                'multiplication' => 'Multiplication',
                'division' => 'Division',
                'powerOf' => 'Power of',
                'remainder' => 'Remainder',
                'toString' => 'To string',
                'lower' => 'Lower',
                'upper' => 'Upper',
                'capitalize' => 'Capitalize',
                'trim' => 'Trim',
                'trimLeft' => 'Trim left',
                'trimRight' => 'Trim right',
                'removeWhitespace' => 'Remove whitespace',
                'length' => 'Length',
                'hash' => 'Hash',
                'split' => 'Split',
                'configurations' => [
                    'position' => 'Position',
                    'digit' => 'Digit',
                    'hash_method' => 'Hash method',
                    'separator' => 'Separator',
                ],
            ],
        ],
    ],
];
