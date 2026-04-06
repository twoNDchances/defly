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
            'word_type' => 'Type',
            'word_file' => 'File format',
            'word_json' => 'JSON format',
            'word_count' => 'Word count',
        ],
        'extras' => [
            'word' => 'Word',
        ],
    ],
];
