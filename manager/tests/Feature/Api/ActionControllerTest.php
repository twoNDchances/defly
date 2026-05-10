<?php

namespace Tests\Feature\Api;

use App\Enums\Action\Type as ActionType;
use App\Enums\Method;
use App\Models\Action;
use Illuminate\Support\Str;

class ActionControllerTest extends ApiTestCase
{
    public function test_actions_api_requires_basic_auth_and_token(): void
    {
        $this->assertApiAuthRequired($this->apiRoute('actions', 'index'));
    }

    public function test_actions_payload_endpoint_is_accessible(): void
    {
        $this->apiJson('GET', $this->apiRoute('actions', 'payload'))->assertOk();
    }

    public function test_actions_store_supports_all_action_types(): void
    {
        foreach ($this->actionPayloads() as $case => $payload) {
            $payload['name'] = $payload['name'].'-'.Str::lower(Str::random(6));

            $response = $this->apiJson('POST', $this->apiRoute('actions', 'store'), [], $payload)
                ->assertCreated();

            $action = Action::query()->findOrFail((string) $response->json('id'));

            $this->assertSame($payload['type'], $action->type->value, "Failed case: {$case}");
        }
    }

    public function test_actions_api_crud_validation_and_put_patch_behavior(): void
    {
        $this->apiJson('GET', $this->apiRoute('actions', 'index'))->assertOk();
        $this->apiJson('POST', $this->apiRoute('actions', 'store'), [], [])->assertUnprocessable();

        $storePayload = [
            'name' => 'action-request',
            'type' => ActionType::Request->value,
            'request_url' => 'https://example.com/webhook',
            'request_method' => Method::Post->value,
            'request_headers' => [['key' => 'content-type', 'value' => 'application/json']],
            'request_body' => '{"event":"blocked"}',
            'description' => 'Request action.',
        ];

        $storeResponse = $this->apiJson('POST', $this->apiRoute('actions', 'store'), [], $storePayload)
            ->assertCreated();

        $actionId = (string) $storeResponse->json('id');
        $action = Action::query()->findOrFail($actionId);

        $this->assertSame('https://example.com/webhook', data_get($action->configurations, 'url'));
        $this->assertSame('post', data_get($action->configurations, 'method'));

        $this->apiJson('GET', $this->apiRoute('actions', 'show'), ['action' => $actionId])
            ->assertOk()
            ->assertJsonPath('id', $actionId);

        $this->apiJson('PATCH', $this->apiRoute('actions', 'update'), ['action' => $actionId], [
            'description' => 'Patched action.',
        ])->assertOk()->assertJsonPath('description', 'Patched action.');

        $this->apiJson('PUT', $this->apiRoute('actions', 'update'), ['action' => $actionId], [
            'description' => 'Only description',
        ])->assertUnprocessable();

        $putPayload = [
            'name' => 'action-deny',
            'type' => ActionType::Deny->value,
            'deny_status' => 403,
            'deny_content_type' => 'json',
            'deny_body' => '{"message":"Forbidden"}',
            'description' => 'Denied action.',
        ];

        $this->apiJson('PUT', $this->apiRoute('actions', 'update'), ['action' => $actionId], $putPayload)
            ->assertOk()
            ->assertJsonPath('name', 'action-deny');

        $this->apiJson('DELETE', $this->apiRoute('actions', 'destroy'), ['action' => $actionId])->assertNoContent();
        $this->assertDatabaseMissing('actions', ['id' => $actionId]);
    }

    private function actionPayloads(): array
    {
        return [
            'allow' => [
                'name' => 'allow-api',
                'type' => ActionType::Allow->value,
                'description' => 'Allow action',
            ],
            'deny' => [
                'name' => 'deny-api',
                'type' => ActionType::Deny->value,
                'deny_status' => 403,
                'deny_content_type' => 'json',
                'deny_body' => '{"message":"Forbidden"}',
                'description' => 'Deny action',
            ],
            'log' => [
                'name' => 'log-api',
                'type' => ActionType::Log->value,
                'log_format' => '[%time%] %method% %path%',
                'log_console' => true,
                'log_file' => true,
                'description' => 'Log action',
            ],
            'request' => [
                'name' => 'request-api',
                'type' => ActionType::Request->value,
                'request_url' => 'https://example.com/webhook',
                'request_method' => Method::Post->value,
                'request_headers' => [['key' => 'content-type', 'value' => 'application/json']],
                'request_body' => '{"event":"blocked"}',
                'description' => 'Request action',
            ],
            'report' => [
                'name' => 'report-api',
                'type' => ActionType::Report->value,
                'description' => 'Report action',
            ],
            'suspect' => [
                'name' => 'suspect-api',
                'type' => ActionType::Suspect->value,
                'suspect_severity' => 'warning',
                'description' => 'Suspect action',
            ],
            'setter-set' => [
                'name' => 'setter-set-api',
                'type' => ActionType::Setter->value,
                'setter_directive' => 'set',
                'setter_set' => [
                    ['key' => 'risk-score', 'datatype' => 'number', 'value' => '10'],
                ],
                'description' => 'Setter set action',
            ],
            'setter-unset' => [
                'name' => 'setter-unset-api',
                'type' => ActionType::Setter->value,
                'setter_directive' => 'unset',
                'setter_unset' => [
                    ['key' => 'debug-token'],
                ],
                'description' => 'Setter unset action',
            ],
            'score' => [
                'name' => 'score-api',
                'type' => ActionType::Score->value,
                'score_behavior' => '+',
                'score_value' => 5,
                'description' => 'Score action',
            ],
            'level' => [
                'name' => 'level-api',
                'type' => ActionType::Level->value,
                'level_behavior' => 'increase',
                'level_value' => 1,
                'description' => 'Level action',
            ],
        ];
    }
}
