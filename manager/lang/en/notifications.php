<?php

return [
    'actions' => [
        'open' => 'Open',
        'view_defender' => 'View defender',
        'view_principle' => 'View principle',
    ],

    'defender' => [
        'deployment' => [
            'processing' => 'Deployment is being processed.',
            'request_failed' => 'Orchestrator deployment request failed.',
            'exception' => 'Unhandled exception while processing defender deployment.',
            'completed' => [
                'title' => 'Defender deployment completed',
                'body' => '":name" was deployed successfully.',
            ],
            'failed' => [
                'title' => 'Defender deployment failed',
            ],
        ],
        'cancellation' => [
            'queued' => 'Cancel request queued.',
            'request_failed' => 'Orchestrator cancel request failed.',
            'exception' => 'Unhandled exception while canceling defender.',
            'completed' => [
                'title' => 'Defender cancellation completed',
                'body' => '":name" was cancelled successfully.',
            ],
            'failed' => [
                'title' => 'Defender cancellation failed',
            ],
        ],
        'failure' => [
            'body' => '":name": :detail',
            'default_detail' => 'See deployment details for more information.',
            'http_status' => 'HTTP status: :status.',
        ],
        'guard' => [
            'denied' => 'The requester is not assigned to an active guard for this defender.',
        ],
        'action' => [
            'skipped' => [
                'title' => 'Defender action skipped',
                'missing' => 'The selected defender no longer exists.',
                'not_deployed' => '":name" must be deployed successfully before this action can run.',
                'empty' => 'No resources were selected for this action.',
                'guard_denied' => 'The requester is not assigned to an active guard for ":name".',
            ],
        ],
    ],

    'principle' => [
        'action' => [
            'skipped' => [
                'title' => 'Principle action skipped',
                'not_attached' => 'No selected principles are attached to ":name".',
            ],
        ],
        'validation' => [
            'skipped' => [
                'title' => 'Principle validation skipped',
                'missing' => 'The selected principle no longer exists.',
            ],
            'passed' => [
                'title' => 'Principle validation passed',
            ],
            'failed' => [
                'title' => 'Principle validation failed',
            ],
            'finished' => '":name" finished validation with :count error(s).',
            'exception' => 'Unhandled exception while validating principle.',
            'exception_body' => '":name": :message',
        ],
    ],

    'decision' => [
        'action' => [
            'skipped' => [
                'title' => 'Decision action skipped',
                'not_attached' => 'No selected decisions are attached to ":name".',
            ],
        ],
    ],

    'communication' => [
        'actions' => [
            'apply' => 'apply',
            'revoke' => 'revoke',
            'implement' => 'implement',
            'suspend' => 'suspend',
        ],
        'resources' => [
            'principle' => '{1} principle|[2,*] principles',
            'decision' => '{1} decision|[2,*] decisions',
        ],
        'result_body' => [
            'successful' => 'Completed :action for :count :resource on ":defender". HTTP status: :status.',
            'failed' => 'Failed to :action :count :resource on ":defender". HTTP status: :status.',
            'exception' => 'Failed to :action :count :resource on ":defender". :message',
        ],
        'titles' => [
            'principle' => [
                'apply' => [
                    'completed' => 'Principle apply completed',
                    'failed' => 'Principle apply failed',
                ],
                'revoke' => [
                    'completed' => 'Principle revoke completed',
                    'failed' => 'Principle revoke failed',
                ],
            ],
            'decision' => [
                'implement' => [
                    'completed' => 'Decision implement completed',
                    'failed' => 'Decision implement failed',
                ],
                'suspend' => [
                    'completed' => 'Decision suspend completed',
                    'failed' => 'Decision suspend failed',
                ],
            ],
        ],
    ],
];
