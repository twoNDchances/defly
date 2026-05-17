<?php

namespace Tests\Feature\Gui\FilamentData;

use Tests\Support\FilamentRuleDataHarness;
use Tests\Support\FilamentTestHelpers;
use Tests\TestCase;

class RuleDataTest extends TestCase
{
    use FilamentTestHelpers;

    public function test_rule_data_transforms_all_comparator_configurations(): void
    {
        foreach ($this->rulePayloads() as $payload) {
            $saved = FilamentRuleDataHarness::saveForm($payload);
            $this->assertArrayHasKey('configurations', $saved);
            FilamentRuleDataHarness::loadForm(['comparator' => $payload['comparator'], 'configurations' => $saved['configurations'] ?? []]);
        }
    }
}
