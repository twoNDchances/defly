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
        'buttons' => [
            'generate_password' => 'Generate password',
        ],
        'sections' => [
            'a' => [
                'title' => 'User definition',
            ],
        ],
    ],
    'permission' => [
        'text_examples' => [
            'name' => 'Model:Action',
            'description' => 'Some description about this permission',
        ],
        'descriptions' => [
            'name' => 'A unique name to represent this permission',
            'applied_for' => 'Scope of application',
            'action' => 'Actions are permitted within the selected scope',
            'description' => 'You can explain in more detail if this permission is complex',
        ],
        'sections' => [
            'a' => [
                'title' => 'Permission definition',
            ],
        ],
    ],
];
