<?php

namespace Tests\Feature\Api;

use App\Enums\Action\Type as ActionType;
use App\Enums\Decision\Action as DecisionAction;
use App\Enums\Decision\Condition;
use App\Enums\Decision\Direction;
use App\Models\Action;
use App\Models\Decision;
use Illuminate\Support\Str;

class DecisionControllerTest extends ApiTestCase
{
    public function test_decisions_payload_endpoint_is_accessible(): void
    {
        $this->apiJson('GET', $this->apiRoute('decisions', 'payload'))->assertOk();
    }

    public function test_decisions_store_supports_all_action_configurations(): void
    {
        $denyAction = Action::query()->create([
            'name' => 'deny-record-'.Str::lower(Str::random(6)),
            'type' => ActionType::Deny->value,
            'configurations' => [
                'status' => 403,
                'content_type' => 'json',
                'body' => '{"message":"Forbidden"}',
            ],
            'description' => 'Deny template',
        ]);

        foreach ($this->decisionPayloads((string) $denyAction->id) as $payload) {
            $payload['name'] = $payload['name'].'-'.Str::lower(Str::random(6));

            $this->apiJson('POST', $this->apiRoute('decisions', 'store'), [], $payload)
                ->assertCreated();
        }
    }

    public function test_decisions_reject_invalid_action_for_direction(): void
    {
        $this->apiJson('POST', $this->apiRoute('decisions', 'store'), [], [
            'name' => 'invalid-direction-action',
            'direction' => Direction::Response->value,
            'condition' => Condition::GreaterThanOrEqual->value,
            'score' => 5,
            'action' => DecisionAction::Redirect->value,
        ])->assertUnprocessable()->assertJsonValidationErrors(['action']);
    }

    public function test_decisions_api_crud_validation_and_put_patch_behavior(): void
    {
        $this->apiJson('GET', $this->apiRoute('decisions', 'index'))->assertOk();
        $this->apiJson('POST', $this->apiRoute('decisions', 'store'), [], [])->assertUnprocessable();

        $storePayload = [
            'name' => 'decision-rewrite-query',
            'direction' => Direction::Request->value,
            'condition' => Condition::GreaterThanOrEqual->value,
            'score' => 5,
            'action' => DecisionAction::Rewrite->value,
            'rewrite_type' => 'query',
            'rewrite_query_directive' => 'set',
            'rewrite_query_set' => [['key' => 'reviewed', 'value' => '1']],
            'description' => 'Rewrite query decision.',
        ];

        $storeResponse = $this->apiJson('POST', $this->apiRoute('decisions', 'store'), [], $storePayload)
            ->assertCreated();

        $decisionId = (string) $storeResponse->json('id');
        $decision = Decision::query()->findOrFail($decisionId);

        $this->assertSame('query', data_get($decision->configurations, 'type'));
        $this->assertSame('set', data_get($decision->configurations, 'query.directive'));

        $this->apiJson('GET', $this->apiRoute('decisions', 'show'), ['decision' => $decisionId])
            ->assertOk()
            ->assertJsonPath('id', $decisionId);

        $this->apiJson('PATCH', $this->apiRoute('decisions', 'update'), ['decision' => $decisionId], [
            'description' => 'Patched decision.',
        ])->assertOk()->assertJsonPath('description', 'Patched decision.');

        $this->apiJson('PUT', $this->apiRoute('decisions', 'update'), ['decision' => $decisionId], [
            'description' => 'Only description',
        ])->assertUnprocessable();

        $putPayload = [
            'name' => 'decision-allow',
            'direction' => Direction::Request->value,
            'condition' => Condition::Equal->value,
            'score' => 5,
            'action' => DecisionAction::Allow->value,
            'description' => 'Allow decision.',
        ];

        $this->apiJson('PUT', $this->apiRoute('decisions', 'update'), ['decision' => $decisionId], $putPayload)
            ->assertOk()
            ->assertJsonPath('name', 'decision-allow');

        $this->apiJson('DELETE', $this->apiRoute('decisions', 'destroy'), ['decision' => $decisionId])->assertNoContent();
        $this->assertDatabaseMissing('decisions', ['id' => $decisionId]);
    }

    private function decisionPayloads(string $denyActionId): array
    {
        $base = [
            'direction' => Direction::Request->value,
            'condition' => Condition::GreaterThanOrEqual->value,
            'score' => 5,
            'description' => 'Decision API example',
        ];

        return [
            [...$base, 'name' => 'allow-request', 'action' => DecisionAction::Allow->value],
            [...$base, 'name' => 'deny-default', 'action' => DecisionAction::Deny->value, 'deny_directive' => 'use_default'],
            [...$base, 'name' => 'deny-copy-record', 'action' => DecisionAction::Deny->value, 'deny_directive' => 'copy_record', 'deny_record' => $denyActionId],
            [...$base, 'name' => 'rewrite-headers-set', 'action' => DecisionAction::RewriteHeaders->value, 'rewrite_headers_directive' => 'set', 'rewrite_headers_set' => [['key' => 'x-defly-decision', 'value' => 'reviewed']]],
            [...$base, 'name' => 'rewrite-headers-unset', 'action' => DecisionAction::RewriteHeaders->value, 'rewrite_headers_directive' => 'unset', 'rewrite_headers_unset' => [['key' => 'x-debug-token']]],
            [...$base, 'name' => 'rewrite-body-set', 'action' => DecisionAction::RewriteBody->value, 'rewrite_body_directive' => 'set', 'rewrite_body_set' => [['key' => 'security.status', 'value' => 'blocked']]],
            [...$base, 'name' => 'rewrite-body-unset', 'action' => DecisionAction::RewriteBody->value, 'rewrite_body_directive' => 'unset', 'rewrite_body_unset' => [['key' => 'debug.trace']]],
            [...$base, 'name' => 'redirect-request', 'action' => DecisionAction::Redirect->value, 'redirect_url' => 'https://example.com/blocked'],
            [...$base, 'name' => 'cancel-request', 'action' => DecisionAction::Cancel->value],
            [...$base, 'name' => 'rewrite-path', 'action' => DecisionAction::Rewrite->value, 'rewrite_type' => 'path', 'rewrite_path' => '/safe-path'],
            [...$base, 'name' => 'rewrite-query-set', 'action' => DecisionAction::Rewrite->value, 'rewrite_type' => 'query', 'rewrite_query_directive' => 'set', 'rewrite_query_set' => [['key' => 'reviewed', 'value' => '1']]],
            [...$base, 'name' => 'rewrite-query-unset', 'action' => DecisionAction::Rewrite->value, 'rewrite_type' => 'query', 'rewrite_query_directive' => 'unset', 'rewrite_query_unset' => [['key' => 'debug']]],
            [...$base, 'name' => 'save-request', 'action' => DecisionAction::Save->value, 'save_position' => 'prefix', 'save_name' => 'request.json'],
            [...$base, 'name' => 'allow-response', 'direction' => Direction::Response->value, 'action' => DecisionAction::Allow->value],
            [...$base, 'name' => 'erase-cookies', 'direction' => Direction::Response->value, 'action' => DecisionAction::EraseCookies->value],
            [...$base, 'name' => 'force-no-cache', 'direction' => Direction::Response->value, 'action' => DecisionAction::ForceNoCache->value],
        ];
    }
}
