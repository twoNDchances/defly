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
    'guard' => [
        'text_examples' => [
            'name' => 'guard-protect-defender',
        ],
        'descriptions' => [
            'name' => 'A unique kebab-case name for this guard',
            'expired_at' => 'Optional time when this guard expires',
        ],
        'sections' => [
            'a' => [
                'title' => 'Guard definition',
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
    'rule' => [
        'text_examples' => [
            'name' => 'rule',
        ],
        'descriptions' => [
            'name' => 'A unique kebab-case name for this rule',
            'phase' => 'Execution phase where the target data can be obtained',
            'target' => 'Target selected for comparison',
            'comparator' => 'Compare the extracted target value with provided values across different datatypes',
            'is_inversed' => 'Invert the comparison result',
            'wordlist' => 'Select a wordlist used by the comparator for the value to compare',
        ],
        'steps' => [
            'a' => [
                'title' => 'Rule preparation',
            ],
            'b' => [
                'title' => 'Rule definition',
            ],
        ],
        'extras' => [
            'comparator' => [
                '@similar' => '[(Target){Array} @ (Value){Wordlist}] True if a target array item equals an item in the provided wordlist',
                '@contains' => '[(Target){Array} @ (Value){String}] True if a target array item equals the provided string value',
                '@match' => '[(Target){Array} @ (Value){String}] True if a target array item matches the provided string using a regular expression',
                '@search' => '[(Target){Array} @ (Value){Wordlist}] True if a target array item matches an item in the provided wordlist using a regular expression',
                '@equal' => '[(Target){Number} @ (Value){Number}] True if the target number equals the provided number',
                '@greaterThan' => '[(Target){Number} @ (Value){Number}] True if the target number is greater than the provided number',
                '@lessThan' => '[(Target){Number} @ (Value){Number}] True if the target number is less than the provided number',
                '@greaterThanOrEqual' => '[(Target){Number} @ (Value){Number}] True if the target number is greater than or equal to the provided number',
                '@lessThanOrEqual' => '[(Target){Number} @ (Value){Number}] True if the target number is less than or equal to the provided number',
                '@inRange' => '[(Target){Number} @ (Value){Number range}] True if the target number is within the range from smaller to larger value',
                '@mirror' => '[(Target){String} @ (Value){String}] True if the target string equals the provided string',
                '@startsWith' => '[(Target){String} @ (Value){String}] True if the target string starts with the provided string',
                '@endsWith' => '[(Target){String} @ (Value){String}] True if the target string ends with the provided string',
                '@check' => '[(Target){String} @ (Value){Wordlist}] True if the target string equals an item in the provided wordlist',
                '@regExp' => '[(Target){String} @ (Value){String}] True if the target string matches the provided string using a regular expression',
                '@checkRegExp' => '[(Target){String} @ (Value){Wordlist}] True if the target string matches an item in the provided wordlist using a regular expression',
            ],
            'configurations' => [
                'string_value' => 'String value used to compare with targets whose final datatype is string',
                'number_value' => 'Numeric value used to compare with targets whose final datatype is number',
                'number_from_value' => 'Range start number',
                'number_to_value' => 'Range end number',
            ],
        ],
    ],
    'principle' => [
        'text_examples' => [
            'name' => 'principle',
            'level' => '1',
        ],
        'descriptions' => [
            'name' => 'A unique kebab-case name for this principle',
            'level' => 'Violation level this principle belongs to. The principle is skipped if this value is higher than the violation level configured in Defender',
            'phase' => 'Phase where the principle will be executed',
            'validation_status' => 'Status that decides whether the principle can be applied to Defender',
            'validation_details' => 'Detailed validation results so you can identify principle errors',
        ],
        'tabs' => [
            'a' => [
                'title' => 'Principle definition',
            ],
            'b' => [
                'title' => 'Principle status',
            ],
        ],
        'extras' => [
            'validation_status' => [
                'pending' => 'Waiting for validation',
                'validating' => 'Validating all requirements before apply',
                'failed' => 'Validation failed, not ready to apply',
                'passed' => 'Validation passed, ready to apply',
            ],
        ],
    ],
    'decision' => [
        'text_examples' => [
            'name' => 'decision',
            'score' => '5',
        ],
        'descriptions' => [
            'name' => 'A unique kebab-case name for this decision',
            'direction' => 'Direction that triggers this decision action. Inbound is request, outbound is response',
            'condition' => 'Condition type that triggers the action when the expected score is reached',
            'score' => 'Violation score used as the condition for triggering the action',
            'action' => 'Action triggered when the condition is met',
        ],
        'buttons' => [
            'test_request_button' => 'Test send',
            'test_request_button_success' => 'Test request sent',
            'test_request_button_empty' => 'No URL available for test sending',
            'test_request_button_failed' => 'Could not send the test request',
        ],
        'sections' => [
            'a' => [
                'title' => 'Decision definition',
            ],
        ],
        'extras' => [
            'direction' => [
                'request' => 'Inbound: HTTP request',
                'response' => 'Outbound: HTTP response',
            ],
            'condition' => [
                '<' => 'Trigger the action if lower than the violation score',
                '<=' => 'Trigger the action if lower than or equal to the violation score',
                '=' => 'Trigger the action if equal to the violation score',
                '>=' => 'Trigger the action if greater than or equal to the violation score',
                '>' => 'Trigger the action if greater than the violation score',
            ],
            'action' => [
                'allow' => 'Stop all decisions in the current direction and allow the flow to pass',
                'deny' => 'Stop all decisions in the current direction and deny the flow',
                'rewrite_headers' => 'Add, remove, or update headers for the current direction before passing through',
                'rewrite_body' => 'Add, remove, or rewrite body content for the current direction before passing through',
                'redirect' => 'Stop all decisions and redirect the HTTP request to another backend',
                'cancel' => 'Stop all decisions, cancel the HTTP request, and return no response',
                'rewrite' => 'Rewrite HTTP request metadata',
                'save' => 'Save the HTTP request in the desired format for investigation',
                'erase_cookies' => 'Cookies returned in the HTTP response will not be returned to the client',
                'force_no_cache' => 'The HTTP response will not be cached; all resources are always reloaded for the next HTTP request',
            ],
            'configurations' => [
                'deny_directive' => 'Choose how to deny the HTTP request',
                'deny_record' => 'Deny action record used to copy configurations',
                'rewrite_headers_directive' => 'Choose whether to add/update or remove headers',
                'rewrite_headers_set' => 'List of headers to add or update',
                'rewrite_headers_unset' => 'List of headers to remove',
                'rewrite_body_directive' => 'Choose whether to add/update or remove body content',
                'rewrite_body_set' => 'List of body keys to add or update',
                'rewrite_body_unset' => 'List of body keys to remove',
                'rewrite_type' => 'Choose whether to rewrite the path or URL query parameters',
                'rewrite_path' => 'Path after the URL, starting with /',
                'rewrite_query_directive' => 'Choose whether to add/update or remove URL query parameters',
                'rewrite_query_set' => 'List of URL query parameters to add or update',
                'rewrite_query_unset' => 'List of URL query parameters to remove',
                'redirect_url' => 'Replacement backend URL for sending the HTTP request',
                'save_position' => 'Choose whether the saved filename uses a prefix or suffix',
                'save_name' => 'Filename used when saving the HTTP request',
            ],
            'key' => 'Used to identify the content',
            'value' => 'Used to store the content',
        ],
    ],
    'defender' => [
        'text_examples' => [
            'name' => 'defender-1',
            'proxy_port' => '9948',
        ],
        'descriptions' => [
            'name' => 'A unique kebab-case name representing this defender',
            'proxy_port' => 'Port that Defender Proxy will open after successful deployment',
            'status' => 'Current defender status after deployment',
            'details' => 'Detailed defender status after deployment',
            'deployment_status' => 'Deployment status determines whether this defender can start being used',
            'deploymnet_details' => 'Deployment details to help you identify defender issues',
            'log' => 'Latest defender logs returned by the orchestrator',
            'last_response_details' => 'Most recent response details from Defly Defender, split by principle and decision requests',
        ],
        'extras' => [
            'status' => [
                'normal' => 'Defender is considered to be operating normally',
                'abnormal' => 'Defender is considered to be operating abnormally',
            ],
            'deployment_status' => [
                'pending' => 'Waiting for deployment',
                'processing' => 'Processing',
                'failed' => 'Deployment failed, not ready to use',
                'successful' => 'Deployment successful, ready to use',
            ],
            'log' => [
                'failed_to_follow' => 'Failed to follow defender logs.',
            ],
            'environment_variables' => [

            ],
        ],
        'buttons' => [
            'follow' => 'Follow',
            'refresh' => 'Refresh',
            'tooltips' => [
                'follow' => 'Fetch the latest defender logs from the orchestrator',
                'refresh' => 'Refresh the latest response details from the Defender Server',
            ],
        ],
        'tabs' => [
            'a' => [
                'title' => 'Main',
                'sections' => [
                    'a' => [
                        'title' => 'Defender definition',
                    ],
                ],
            ],
            'b' => [
                'title' => 'Environment variables',
                'sections' => [
                    'a' => [
                        'title' => 'Common',
                    ],
                    'b' => [
                        'title' => 'Server',
                    ],
                    'c' => [
                        'title' => 'Proxy',
                    ],
                ],
            ],
            'c' => [
                'title' => 'Status',
                'sections' => [
                    'a' => [
                        'title' => 'Defender status',
                    ],
                    'b' => [
                        'title' => 'Defender deployment status',
                    ],
                ],
            ],
            'd' => [
                'title' => 'Logs',
                'sections' => [
                    'a' => [
                        'title' => 'Defender logs',
                    ],
                    'b' => [
                        'title' => 'Defender response',
                    ],
                ],
            ],
        ],
    ],
    'key' => [
        'descriptions' => [
            'name' => 'A unique kebab-case name representing this API key',
            'token' => 'Secret token used for API authentication. Leave blank when editing to keep the current token',
            'expired_at' => 'Optional expiration date and time for this key',
            'is_reused' => 'Allow this key to reuse the owner user groups and permissions instead of assigning access directly to the key',
        ],
        'sections' => [
            'a' => [
                'title' => 'Key definition',
            ],
        ],
    ],
    'report' => [
        'sections' => [
            'metas' => [
                'title' => 'HTTP metadata',
            ],
            'request' => [
                'title' => 'Request',
            ],
            'response' => [
                'title' => 'Response',
            ],
            'rule' => [
                'title' => 'Triggered rule',
            ],
        ],
    ],
    'timeline' => [
        'descriptions' => [
            'created_at' => 'When this event happened',
            'created_by' => 'User who triggered this event',
            'ipv4' => 'IPv4 address from the user browser',
            'ipv6' => 'IPv6 address from the user browser',
            'method' => 'HTTP method that triggered this event',
            'path' => 'Path that triggered this event',
            'action' => 'Action performed on this resource',
        ],
        'extras' => [
            'resource' => [
                'resource_type' => 'Targeted resource type',
                'resource_id' => 'Targeted resource ID',
            ],
        ],
        'buttons' => [
            'open_resource' => 'Open resource',
        ],
        'sections' => [
            'a' => [
                'title' => 'Timeline details',
            ],
        ],
    ],
];
