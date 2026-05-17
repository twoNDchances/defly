<?php

namespace Tests\Feature\Gui\FilamentData;

use App\Enums\Action\Type as ActionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FilamentActionDataHarness;
use Tests\TestCase;

class ActionDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_action_data_transforms_all_action_configurations(): void
    {
        $cases = [
            ActionType::Allow->value => ['type' => ActionType::Allow->value],
            ActionType::Deny->value => ['type' => ActionType::Deny->value, 'deny_status' => 403, 'deny_content_type' => 'json', 'deny_body' => '{"message":"no"}'],
            ActionType::Log->value => ['type' => ActionType::Log->value, 'log_format' => '[%time%]', 'log_console' => true, 'log_file' => false],
            ActionType::Request->value => ['type' => ActionType::Request->value, 'request_url' => 'https://example.com', 'request_method' => 'post', 'request_headers' => [['key' => 'x-test', 'value' => '1']], 'request_body' => '{}'],
            ActionType::Suspect->value => ['type' => ActionType::Suspect->value, 'suspect_severity' => 'warning'],
            'setter-set' => ['type' => ActionType::Setter->value, 'setter_directive' => 'set', 'setter_set' => [['key' => 'score', 'datatype' => 'number', 'value' => '1']]],
            'setter-unset' => ['type' => ActionType::Setter->value, 'setter_directive' => 'unset', 'setter_unset' => [['key' => 'score']]],
            ActionType::Score->value => ['type' => ActionType::Score->value, 'score_behavior' => '+', 'score_value' => 3],
            ActionType::Level->value => ['type' => ActionType::Level->value, 'level_behavior' => 'increase', 'level_value' => 1],
        ];

        foreach ($cases as $case => $data) {
            $saved = FilamentActionDataHarness::saveForm($data);
            $this->assertArrayHasKey('configurations', $saved, "Missing configurations for {$case}.");
            $loaded = FilamentActionDataHarness::loadForm([
                'type' => $data['type'],
                'configurations' => $saved['configurations'],
            ]);
            $this->assertSame($data['type'], $loaded['type']);
        }
    }
}
