<?php

namespace App\Http\Controllers;

use App\Enums\Action\Type;
use App\Enums\Method;
use App\Http\Requests\ActionRelationRequest;
use App\Http\Requests\ActionRequest;
use App\Models\Action;
use App\Services\ApiPayload;
use App\Traits\Filament\Specifics\Action\ActionData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ActionController extends Controller
{
    use ActionData;

    public function index(ActionRequest $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->integer('per_page', 15), 100));

        $actions = Action::query()
            ->latest()
            ->paginate($perPage);

        return response()->json($actions);
    }

    public function store(ActionRequest $request): JsonResponse
    {
        $action = Action::query()->create($this->actionData($request));

        return response()->json($action, SymfonyResponse::HTTP_CREATED);
    }

    public function payload(): JsonResponse
    {
        return response()->json(ApiPayload::resource('actions', [
            'store_allow' => [
                'method' => 'POST',
                'body' => [
                    'name' => 'allow-api',
                    'type' => Type::Allow->value,
                    'description' => 'Allow request or response flow.',
                ],
            ],
            'store_deny' => [
                'method' => 'POST',
                'body' => [
                    'name' => 'deny-api',
                    'type' => Type::Deny->value,
                    'deny_status' => 403,
                    'deny_content_type' => 'json',
                    'deny_body' => '{"message":"Forbidden"}',
                    'description' => 'Deny request or response flow.',
                ],
            ],
            'store_log' => [
                'method' => 'POST',
                'body' => [
                    'name' => 'log-api',
                    'type' => Type::Log->value,
                    'log_format' => '[%time%] %ip% | %method% | %path% | %bytesSent% | %bytesReceived% | %error%',
                    'log_console' => true,
                    'log_file' => true,
                    'description' => 'Write proxy activity to configured logs.',
                ],
            ],
            'store_request' => [
                'method' => 'POST',
                'body' => [
                    'name' => 'notify-api',
                    'type' => Type::Request->value,
                    'request_url' => 'https://example.com/webhook',
                    'request_method' => Method::Post->value,
                    'request_headers' => [
                        [
                            'key' => 'content-type',
                            'value' => 'application/json',
                        ],
                    ],
                    'request_body' => '{"event":"blocked"}',
                    'description' => 'Send a webhook request.',
                ],
            ],
            'store_report' => [
                'method' => 'POST',
                'body' => [
                    'name' => 'report-api',
                    'type' => Type::Report->value,
                    'description' => 'Mark the event for reporting.',
                ],
            ],
            'store_suspect' => [
                'method' => 'POST',
                'body' => [
                    'name' => 'suspect-api',
                    'type' => Type::Suspect->value,
                    'suspect_severity' => 'warning',
                    'description' => 'Mark the event as suspicious.',
                ],
            ],
            'store_setter_set' => [
                'method' => 'POST',
                'body' => [
                    'name' => 'setter-set-api',
                    'type' => Type::Setter->value,
                    'setter_directive' => 'set',
                    'setter_set' => [
                        [
                            'key' => 'violation-score',
                            'datatype' => 'number',
                            'value' => '10',
                        ],
                        [
                            'key' => 'review-status',
                            'datatype' => 'string',
                            'value' => 'manual',
                        ],
                    ],
                    'description' => 'Set execution variables.',
                ],
            ],
            'store_setter_unset' => [
                'method' => 'POST',
                'body' => [
                    'name' => 'setter-unset-api',
                    'type' => Type::Setter->value,
                    'setter_directive' => 'unset',
                    'setter_unset' => [
                        ['key' => 'debug-token'],
                    ],
                    'description' => 'Unset execution variables.',
                ],
            ],
            'store_score' => [
                'method' => 'POST',
                'body' => [
                    'name' => 'score-api',
                    'type' => Type::Score->value,
                    'score_behavior' => '+',
                    'score_value' => 5,
                    'description' => 'Adjust violation score.',
                ],
            ],
            'store_level' => [
                'method' => 'POST',
                'body' => [
                    'name' => 'level-api',
                    'type' => Type::Level->value,
                    'level_behavior' => 'increase',
                    'level_value' => 1,
                    'description' => 'Adjust violation level.',
                ],
            ],
            'update' => [
                'method' => 'PATCH',
                'path' => '{action}',
                'body' => [
                    'description' => 'Updated action description.',
                ],
            ],
            'list_labels' => [
                'method' => 'GET',
                'path' => '{action}/labels',
            ],
            'attach_labels' => [
                'method' => 'POST',
                'path' => '{action}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                        '<label-id-2>',
                    ],
                ],
            ],
            'detach_labels' => [
                'method' => 'DELETE',
                'path' => '{action}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                    ],
                ],
            ],
        ]));
    }

    public function show(ActionRequest $request, Action $action): JsonResponse
    {
        return response()->json($action);
    }

    public function update(ActionRequest $request, Action $action): JsonResponse
    {
        $action->update($this->actionData($request));

        return response()->json($action->refresh());
    }

    public function destroy(ActionRequest $request, Action $action): HttpResponse
    {
        $action->delete();

        return response()->noContent();
    }

    public function labels(ActionRelationRequest $request, Action $action): JsonResponse
    {
        return response()->json($action->labels()
            ->latest()
            ->get());
    }

    public function attachLabels(ActionRelationRequest $request, Action $action): JsonResponse
    {
        $action->labels()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($action->labels()
            ->latest()
            ->get());
    }

    public function detachLabels(ActionRelationRequest $request, Action $action): JsonResponse
    {
        $action->labels()->detach($request->validated('ids', []));

        return response()->json($action->labels()
            ->latest()
            ->get());
    }

    private function actionData(ActionRequest $request): array
    {
        $data = $request->validated();
        $data = self::saveForm($data);

        return $this->onlyFields($data, [
            'name',
            'type',
            'configurations',
            'description',
        ]);
    }
}
