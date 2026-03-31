<?php

return [
    'permission' => [
        'name' => 'Permission',
        'fields' => [
            'name' => 'Name',
            'description' => 'Description',
            'applied_for' => 'Applied for',
            'action' => 'Action',
            'created_by' => 'Created by',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ],
    ],
    'policy' => [
        'name' => 'Policy',
        'fields' => [
            'name' => 'Name',
            'description' => 'Description',
            'created_by' => 'Created by',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
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
            'created_by' => 'Created by',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ],
    ],
];
