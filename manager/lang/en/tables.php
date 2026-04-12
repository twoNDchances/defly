<?php

return [
    'commons' => [
        'labels' => 'Labels',
        'created_by' => 'Created by',
        'created_at' => 'Created at',
        'updated_at' => 'Updated at',
    ],
    'columns' => [
        'user' => [
            'name' => 'Name',
            'email' => 'Email',
            'is_verified' => 'Is verified',
            'is_root' => 'Is root',
            'is_activated' => 'Is activated',
            'permissions' => 'Permissions',
            'policies' => 'Policies',
        ],
        'permission' => [
            'name' => 'Name',
            'applied_for' => 'Scope',
            'action' => 'Action',
            'users' => 'Users',
            'policies' => 'Policies',
        ],
        'policy' => [
            'name' => 'Name',
            'users' => 'Users',
            'permissions' => 'Permissions',
        ],
        'label' => [
            'name' => 'Name',
            'color' => 'Color',
            'preview' => 'Preview',
        ],
        'wordlist' => [
            'name' => 'Name',
            'type' => 'Wordlist type',
            'word_count' => 'Word count',
        ],
        'engine' => [
            'name' => 'Name',
            'input_datatype' => 'Datatype before',
            'type' => 'Transformer type',
            'output_datatype' => 'Datatype after',
        ],
        'pattern' => [
            'name' => 'Name',
            'phase' => 'Execution phase',
            'type' => 'Scope type',
            'datatype' => 'Datatype',
            'targets' => 'Targets',
        ],
        'target' => [
            'name' => 'Name',
            'phase' => 'Execution phase',
            'type' => 'Scope type',
            'datatype' => 'Datatype',
            'pattern' => 'Pattern',
            'wordlist' => 'Wordlist',
        ],
    ],
];
