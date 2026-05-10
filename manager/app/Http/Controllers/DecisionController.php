<?php

namespace App\Http\Controllers;

use App\Enums\Decision\Action as DecisionAction;
use App\Enums\Decision\Condition;
use App\Enums\Decision\Direction;
use App\Http\Requests\DecisionRequest;
use App\Models\Decision;
use App\Services\ApiPayload;
use App\Traits\Filament\Specifics\Decision\DecisionData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class DecisionController extends Controller
{
    use DecisionData;

    public function index(DecisionRequest $request): JsonResponse
    {
        $decisions = Decision::query()
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($decisions);
    }

    public function store(DecisionRequest $request): JsonResponse
    {
        $decision = Decision::query()->create($this->decisionData($request));

        return response()->json($decision, SymfonyResponse::HTTP_CREATED);
    }

    public function payload(): JsonResponse
    {
        return response()->json(ApiPayload::resource('decisions', [
            'store_allow_request' => [
                'method' => 'POST',
                'body' => $this->payloadBody('allow-request', Direction::Request->value, DecisionAction::Allow->value),
            ],
            'store_deny_default' => [
                'method' => 'POST',
                'body' => $this->payloadBody('deny-default', Direction::Request->value, DecisionAction::Deny->value, [
                    'deny_directive' => 'use_default',
                ]),
            ],
            'store_deny_copy_record' => [
                'method' => 'POST',
                'body' => $this->payloadBody('deny-copy-record', Direction::Request->value, DecisionAction::Deny->value, [
                    'deny_directive' => 'copy_record',
                    'deny_record' => '<deny-action-id>',
                ]),
            ],
            'store_rewrite_headers_set' => [
                'method' => 'POST',
                'body' => $this->payloadBody('rewrite-headers-set', Direction::Request->value, DecisionAction::RewriteHeaders->value, [
                    'rewrite_headers_directive' => 'set',
                    'rewrite_headers_set' => [
                        ['key' => 'x-defly-decision', 'value' => 'reviewed'],
                    ],
                ]),
            ],
            'store_rewrite_headers_unset' => [
                'method' => 'POST',
                'body' => $this->payloadBody('rewrite-headers-unset', Direction::Request->value, DecisionAction::RewriteHeaders->value, [
                    'rewrite_headers_directive' => 'unset',
                    'rewrite_headers_unset' => [
                        ['key' => 'x-debug-token'],
                    ],
                ]),
            ],
            'store_rewrite_body_set' => [
                'method' => 'POST',
                'body' => $this->payloadBody('rewrite-body-set', Direction::Request->value, DecisionAction::RewriteBody->value, [
                    'rewrite_body_directive' => 'set',
                    'rewrite_body_set' => [
                        ['key' => 'security.status', 'value' => 'blocked'],
                    ],
                ]),
            ],
            'store_rewrite_body_unset' => [
                'method' => 'POST',
                'body' => $this->payloadBody('rewrite-body-unset', Direction::Request->value, DecisionAction::RewriteBody->value, [
                    'rewrite_body_directive' => 'unset',
                    'rewrite_body_unset' => [
                        ['key' => 'debug.trace'],
                    ],
                ]),
            ],
            'store_redirect' => [
                'method' => 'POST',
                'body' => $this->payloadBody('redirect-request', Direction::Request->value, DecisionAction::Redirect->value, [
                    'redirect_url' => 'https://example.com/blocked',
                ]),
            ],
            'store_cancel' => [
                'method' => 'POST',
                'body' => $this->payloadBody('cancel-request', Direction::Request->value, DecisionAction::Cancel->value),
            ],
            'store_rewrite_path' => [
                'method' => 'POST',
                'body' => $this->payloadBody('rewrite-path', Direction::Request->value, DecisionAction::Rewrite->value, [
                    'rewrite_type' => 'path',
                    'rewrite_path' => '/safe-path',
                ]),
            ],
            'store_rewrite_query_set' => [
                'method' => 'POST',
                'body' => $this->payloadBody('rewrite-query-set', Direction::Request->value, DecisionAction::Rewrite->value, [
                    'rewrite_type' => 'query',
                    'rewrite_query_directive' => 'set',
                    'rewrite_query_set' => [
                        ['key' => 'reviewed', 'value' => '1'],
                    ],
                ]),
            ],
            'store_rewrite_query_unset' => [
                'method' => 'POST',
                'body' => $this->payloadBody('rewrite-query-unset', Direction::Request->value, DecisionAction::Rewrite->value, [
                    'rewrite_type' => 'query',
                    'rewrite_query_directive' => 'unset',
                    'rewrite_query_unset' => [
                        ['key' => 'debug'],
                    ],
                ]),
            ],
            'store_save' => [
                'method' => 'POST',
                'body' => $this->payloadBody('save-request', Direction::Request->value, DecisionAction::Save->value, [
                    'save_position' => 'prefix',
                    'save_name' => 'request.json',
                ]),
            ],
            'store_allow_response' => [
                'method' => 'POST',
                'body' => $this->payloadBody('allow-response', Direction::Response->value, DecisionAction::Allow->value),
            ],
            'store_erase_cookies' => [
                'method' => 'POST',
                'body' => $this->payloadBody('erase-cookies', Direction::Response->value, DecisionAction::EraseCookies->value),
            ],
            'store_force_no_cache' => [
                'method' => 'POST',
                'body' => $this->payloadBody('force-no-cache', Direction::Response->value, DecisionAction::ForceNoCache->value),
            ],
            'update' => [
                'method' => 'PATCH',
                'path' => '{decision}',
                'body' => [
                    'description' => 'Updated decision description.',
                ],
            ],
        ]));
    }

    public function show(DecisionRequest $request, Decision $decision): JsonResponse
    {
        return response()->json($decision);
    }

    public function update(DecisionRequest $request, Decision $decision): JsonResponse
    {
        $decision->update($this->decisionData($request));

        return response()->json($decision->refresh());
    }

    public function destroy(DecisionRequest $request, Decision $decision): HttpResponse
    {
        $decision->delete();

        return response()->noContent();
    }

    private function decisionData(DecisionRequest $request): array
    {
        $data = self::saveForm($request->validated());

        return $this->onlyFields($data, [
            'name',
            'direction',
            'condition',
            'score',
            'action',
            'configurations',
            'description',
        ]);
    }

    private function payloadBody(string $name, string $direction, string $action, array $extra = []): array
    {
        return [
            'name' => $name,
            'direction' => $direction,
            'condition' => Condition::GreaterThanOrEqual->value,
            'score' => 5,
            'action' => $action,
            'description' => 'Decision API example.',
            ...$extra,
        ];
    }
}
