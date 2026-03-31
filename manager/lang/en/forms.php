<?php

return [
    'user' => [
        'text_examples' => [
            'name' => 'User A',
            'email' => 'user@example.com',
            'password' => 'P@ssw0rd123',
        ],
        'descriptions' => [
            'name' => 'A simple name for this user',
            'email' => 'A unique email address is required for authentication',
            'password' => 'Strong password for authentication',
            'is_verified' => 'Disable this if you want users to be required to verify their email address before logging in, enable will set it to verified',
            'is_root' => 'Enable this if you want this user to have full privileges in the system',
            'is_activated' => 'Disable if you want to prevent this user from using the system temporarily',
        ],
        'actions' => [
            'generate_password' => 'Generate password'
        ],
        'sections' => [
            'a' => [
                'title' => 'User definition',
            ]
        ],
    ],
];
