<?php

return [
    'commons' => [
        'labels' => [
            'label' => 'Label',
            'description' => 'You can also categorize data for this resource',
        ],
        'sections' => [
            'labels' => [
                'title' => 'Resource labeling',
            ],
        ],
    ],
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
    'policy' => [
        'text_examples' => [
            'name' => 'policy-management',
            'description' => 'Some description about this policy',
        ],
        'descriptions' => [
            'name' => 'A unique kebab-style name represents this policy',
            'description' => 'You can explain in more detail if this policy is complex',
        ],
        'sections' => [
            'a' => [
                'title' => 'Policy definition',
            ],
        ],
    ],
    'label' => [
        'text_examples' => [
            'name' => 'label-resources',
            'description' => 'Some description about this label',
        ],
        'descriptions' => [
            'name' => 'A unique kebab-style name represents this label',
            'color' => 'A color code for this label for easier identification',
            'description' => 'You can explain in more detail if this label is complex',
        ],
        'sections' => [
            'a' => [
                'title' => 'Label definition',
            ],
        ],
    ],
    'wordlist' => [
        'text_examples' => [
            'name' => 'wordlist',
            'description' => 'Some description about this wordlist',
            'word' => 'abc',
        ],
        'descriptions' => [
            'name' => 'A unique kebab-style name represents this wordlist',
            'word_type' => 'Select a type of this wordlist',
            'word_file' => 'A path to content file of this wordlist, use this when you have a file with a large number of words. The words are identified by moving to a new line',
            'word_json' => 'A JSON data format of this wordlist, use it when you can define it here',
            'description' => 'You can explain in more detail if this wordlist is complex',
            'word' => 'Word or letters',
        ],
        'sections' => [
            'a' => [
                'title' => 'Wordlist definition',
            ],
            'b' => [
                'title' => 'Word definitions',
            ],
        ],
    ],
];
