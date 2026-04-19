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
        'specials' => [
            'phase' => [
                1 => 'Related to everything in the request',
                2 => 'Related to everything in the request headers',
                3 => 'Related to everything in the request body',
                4 => 'Related to everything in the response headers',
                5 => 'Related to everything in the response body',
                6 => 'Related to everything in the response',
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
            'method' => [
                'get' => 'GET method for sending an HTTP request',
                'post' => 'POST method for sending an HTTP request',
                'put' => 'PUT method for sending an HTTP request',
                'patch' => 'PATCH method for sending an HTTP request',
                'delete' => 'DELETE method for sending an HTTP request',
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
    'group' => [
        'text_examples' => [
            'name' => 'group-management',
        ],
        'descriptions' => [
            'name' => 'A unique kebab-case name for this group',
        ],
        'sections' => [
            'a' => [
                'title' => 'Group definition',
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
            'output_datatype' => 'Output datatype after conversion',
        ],
        'sections' => [
            'a' => [
                'title' => 'Engine definition',
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
    'action' => [
        'text_examples' => [
            'name' => 'action',
        ],
        'descriptions' => [
            'name' => 'A unique kebab-case name for this action',
            'type' => 'Action type that will be triggered when a rule condition is matched',
            'wordlist' => 'Select a wordlist to define configuration for action types that require multiple parameters',
        ],
        'sections' => [
            'a' => [
                'title' => 'Action definition',
            ],
        ],
        'extras' => [
            'type' => [
                'allow' => 'Stop further actions and allow the request or response to continue',
                'deny' => 'Stop further actions and deny the request or response',
                'log' => 'Log detailed information of the request or response',
                'request' => 'Send an HTTP request',
                'report' => 'Send a report to Manager with details',
                'suspect' => 'Increase score in the HTTP lifecycle (request in, response out) based on severity level',
                'setter' => 'Add, remove, or update transaction variables during an HTTP lifecycle',
                'score' => 'Update the maximum violation score',
                'level' => 'Update the active rule level',
            ],
            'configurations' => [
                'deny_status' => 'Choose a response status when denying',
                'deny_content_type' => 'Response content type when denying',
                'deny_body' => 'Response body when denying',
                'log_format' => 'Desired format for log entries',
                'log_console' => 'Write logs to console output',
                'log_file' => 'Write logs to file',
                'request_url' => 'Target URL for sending the request',
                'request_method' => 'HTTP method used to send the request',
                'request_headers' => 'Add or update headers before sending the HTTP request',
                'request_body' => 'HTTP request body. Body will be converted to query parameters if method is GET',
                'suspect_severity' => 'Increase severity by adding the value of each level',
                'setter_directive' => 'Directive that controls transaction variable management',
                'setter_set' => 'Add or update transaction variables for cross-rule communication',
                'setter_unset' => 'Remove transaction variables to tighten control conditions',
                'score_behavior' => 'Choose how the violation score should increase or decrease',
                'score_value' => 'Value used for violation score',
                'level_behavior' => 'Choose how the violation level should increase or decrease',
                'level_value' => 'Value used for violation level',
            ],
            'deny_content_type' => [
                'html' => 'Return HTML content type when denying',
                'json' => 'Return JSON content type when denying',
            ],
            'key' => 'Used to identify the content',
            'value' => 'Used to store the content',
            'set' => 'Add or update data',
            'unset' => 'Remove data',
            'score_behavior' => [
                'override' => 'Use this when you want to set an exact value',
                '+' => 'Apply addition operator',
                '-' => 'Apply subtraction operator',
                '*' => 'Apply multiplication operator',
                '/' => 'Apply division operator',
            ],
            'level_behavior' => [
                'override' => 'Use this when you want to set an exact value',
                'increase' => 'Increase by unit steps',
                'decrease' => 'Decrease by unit steps',
            ],
        ],
    ],
];
