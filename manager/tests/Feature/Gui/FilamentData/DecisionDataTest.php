<?php

namespace Tests\Feature\Gui\FilamentData;

use App\Enums\Action\Type as ActionType;
use App\Enums\Decision\Action as DecisionAction;
use App\Enums\Decision\Condition;
use App\Enums\Decision\Direction;
use App\Models\Action;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\FilamentDecisionDataHarness;
use Tests\TestCase;

class DecisionDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_decision_data_transforms_all_action_configurations(): void
    {
        $denyAction = Action::query()->create([
            'name' => 'deny-copy-'.Str::lower(Str::random(6)),
            'type' => ActionType::Deny->value,
            'configurations' => ['status' => 451, 'content_type' => 'json', 'body' => '{}'],
        ]);

        $cases = [
            ['action' => DecisionAction::Deny->value, 'deny_directive' => 'copy_record', 'deny_record' => $denyAction->id],
            ['action' => DecisionAction::RewriteHeaders->value, 'rewrite_headers_directive' => 'set', 'rewrite_headers_set' => [['key' => 'x-a', 'value' => 'b']]],
            ['action' => DecisionAction::RewriteHeaders->value, 'rewrite_headers_directive' => 'unset', 'rewrite_headers_unset' => [['key' => 'x-a']]],
            ['action' => DecisionAction::RewriteBody->value, 'rewrite_body_directive' => 'set', 'rewrite_body_set' => [['key' => 'a', 'value' => 'b']]],
            ['action' => DecisionAction::RewriteBody->value, 'rewrite_body_directive' => 'unset', 'rewrite_body_unset' => [['key' => 'a']]],
            ['action' => DecisionAction::Redirect->value, 'redirect_url' => 'https://example.com'],
            ['action' => DecisionAction::Rewrite->value, 'rewrite_type' => 'path', 'rewrite_path' => '/clean'],
            ['action' => DecisionAction::Rewrite->value, 'rewrite_type' => 'query', 'rewrite_query_directive' => 'set', 'rewrite_query_set' => [['key' => 'safe', 'value' => '1']]],
            ['action' => DecisionAction::Rewrite->value, 'rewrite_type' => 'query', 'rewrite_query_directive' => 'unset', 'rewrite_query_unset' => [['key' => 'debug']]],
            ['action' => DecisionAction::Save->value, 'save_position' => 'prefix', 'save_name' => 'request.json'],
            ['action' => DecisionAction::Allow->value],
        ];

        foreach ($cases as $case) {
            $data = ['direction' => Direction::Request->value, 'condition' => Condition::GreaterThanOrEqual->value, 'score' => 5, ...$case];
            $saved = FilamentDecisionDataHarness::saveForm($data);
            $this->assertArrayHasKey('configurations', $saved);
            $loaded = FilamentDecisionDataHarness::loadForm(['action' => $case['action'], 'configurations' => $saved['configurations']]);
            $this->assertSame($case['action'], $loaded['action']);
        }
    }
}
