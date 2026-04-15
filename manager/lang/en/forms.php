<?php

return [
    'generals' => [
        'bases' => [
            'fields' => [
                'description' => [
                    'text_examples' => 'Some description about this resource',
                    'descriptions' => 'You can explain in more detail if this resource is complex',
                ],
            ],
            'sections' => [
                'labels' => [
                    'title' => 'Resource labeling',
                    'description' => 'You can also categorize data for this resource',
                ],
            ],
        ],
        'specifics' => [
            'phase' => [
                '1' => 'Related to everything in the request',
                '2' => 'Related to everything in the request headers',
                '3' => 'Related to everything in the request body',
                '4' => 'Related to everything in the response headers',
                '5' => 'Related to everything in the response body',
                '6' => 'Related to everything in the response',
            ],
            'type' => [
                'getter' => 'Search by key and retrieve the value of a variable within a request or response lifecycle, where this variable is accessible at all phases',
                'full' => 'Build complete data of a request or response',
                'header' => 'Find and retrieve the key or value related to request or response headers',
                'meta' => 'Find and retrieve values related to request or response metadata',
                'query' => 'Find and retrieve the key or value associated with URL query parameters of the request',
                'body' => 'Find and retrieve the key or value related to request or response body',
                'file' => 'Find and retrieve the key or value related to files in request or response',
            ],
            'datatype' => [
                'array' => 'Array of string data',
                'number' => 'Number data, including integer and float',
                'string' => 'String data',
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
            'email' => 'A unique email address for authentication',
            'password' => 'A strong password for authentication',
            'is_verified' => 'Disable if you want users to verify email before login, enable to mark as verified',
            'is_root' => 'Enable if you want this user to have full system privileges',
            'is_activated' => 'Disable if you want to temporarily prevent this user from using the system',
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
            'name' => 'Scope:Permission',
        ],
        'descriptions' => [
            'name' => 'A unique name to represent this permission',
            'applied_for' => 'Scope this permission applies to',
            'action' => 'Allowed action within the selected scope',
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
        ],
        'descriptions' => [
            'name' => 'A unique kebab-case name for this policy',
        ],
        'sections' => [
            'a' => [
                'title' => 'Policy definition',
            ],
        ],
    ],
    'label' => [
        'text_examples' => [
            'name' => 'resource-label',
        ],
        'descriptions' => [
            'name' => 'A unique kebab-case name for this label',
            'color' => 'A color code to make this label easier to identify',
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
            'word' => 'abc',
        ],
        'descriptions' => [
            'name' => 'A unique kebab-case name for this wordlist',
            'type' => 'Choose a type of wordlist',
            'word_file' => 'Path to the wordlist content file. Use this when you have a file with many words; words are identified by line breaks',
            'word_json' => 'JSON data for this wordlist. Use this when you can define the content directly here',
            'word' => 'A word or characters',
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
    'engine' => [
        'text_examples' => [
            'name' => 'transformer-engine',
        ],
        'descriptions' => [
            'name' => 'A unique kebab-case name for this engine',
            'input_datatype' => 'Input datatype to be converted',
            'type' => 'Select an engine type suitable for the input datatype',
            'configurations' => 'Depending on engine type, custom configuration values may be required',
            'output_datatype' => 'Output datatype after conversion',
        ],
        'sections' => [
            'a' => [
                'title' => 'Engine definition',
                'fieldsets' => [
                    'a' => [
                        'title' => 'Transformer',
                    ],
                    'b' => [
                        'title' => 'Configurations',
                    ],
                ],
            ],
        ],
        'extras' => [
            'type' => [
                'indexOf' => 'Get the position in an array ([...][index])',
                'merge' => 'Merge all elements together separated by a character ("abc,def")',
                'addition' => 'Addition (+)',
                'subtraction' => 'Subtraction (-)',
                'multiplication' => 'Multiplication (*)',
                'division' => 'Division (/)',
                'powerOf' => 'Power (^)',
                'remainder' => 'Remainder (%)',
                'toString' => 'Convert to string datatype ("1")',
                'lower' => 'All lowercase ("abc def")',
                'upper' => 'All uppercase ("ABC DEF")',
                'capitalize' => 'Capitalize each word ("Abc Def")',
                'trim' => 'Trim spaces on both sides ("abc def")',
                'trimLeft' => 'Trim space on the left ("abc def ")',
                'trimRight' => 'Trim space on the right (" abc def")',
                'removeWhitespace' => 'Remove all spaces ("abcdef")',
                'length' => 'Get string length (7)',
                'hash' => 'Get hash value ("e80b50...")',
                'split' => 'Split string into elements by a specific character (["a", "b", "c", ...])',
            ],
            'configurations' => [
                'position' => 'A specific position in the array',
                'digit' => 'A specific number used in the operation',
                'hash_method' => 'Hash method used to hash input value',
                'separator' => 'You can choose one or multiple characters',
            ],
        ],
    ],
    'pattern' => [
        'descriptions' => [
            'name' => 'Pattern name used to determine which data should be retrieved',
            'phase' => 'Execution phase where data can be retrieved',
            'type' => 'Scope type where data appears',
            'datatype' => 'Datatype returned by the matched pattern',
            'targets' => 'Choose one or multiple targets to apply this pattern',
            'description' => 'Pattern description',
        ],
        'sections' => [
            'a' => [
                'title' => 'Pattern definition',
            ],
            'b' => [
                'title' => 'Implementation targets',
            ],
        ],
    ],
    'target' => [
        'text_examples' => [
            'name' => 'investigation-target',
        ],
        'descriptions' => [
            'name' => 'A unique kebab-case name for this target',
            'phase' => 'Execution phase where data can be retrieved',
            'type' => 'Scope type where data appears',
            'datatype' => 'Datatype returned by this target',
            'pattern' => 'Select an existing pattern to define this target',
            'wordlist' => 'Select a wordlist to define this target when datatype is array',
        ],
        'steps' => [
            'a' => [
                'title' => 'Target preparation',
            ],
            'b' => [
                'title' => 'Target definition',
            ],
        ],
    ],
];