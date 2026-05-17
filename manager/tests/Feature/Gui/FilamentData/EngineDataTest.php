<?php

namespace Tests\Feature\Gui\FilamentData;

use Tests\Support\FilamentEngineDataHarness;
use Tests\Support\FilamentTestHelpers;
use Tests\TestCase;

class EngineDataTest extends TestCase
{
    use FilamentTestHelpers;

    public function test_engine_data_transforms_all_engine_configurations(): void
    {
        foreach ($this->enginePayloads() as $payload) {
            $saved = FilamentEngineDataHarness::saveForm($payload);
            $this->assertArrayHasKey('output_datatype', $saved);
            FilamentEngineDataHarness::loadForm(['type' => $payload['type'], 'configurations' => $saved['configurations'] ?? []]);
        }
    }
}
