<?php

namespace Tests\Feature\Gui\FilamentData;

use Tests\Support\FilamentDefenderDataHarness;
use Tests\TestCase;

class DefenderDataTest extends TestCase
{
    public function test_defender_data_merges_and_loads_environment_variable_groups(): void
    {
        $saved = FilamentDefenderDataHarness::saveForm([
            'common_environment_variables' => [['key' => 'DATABASE_HOST', 'value' => 'db']],
            'server_environment_variables' => [['key' => 'SERVER_PORT', 'value' => '9443']],
            'proxy_environment_variables' => [['key' => 'PROXY_BACKEND_URL', 'value' => 'http://backend']],
        ]);

        $this->assertArrayNotHasKey('DATABASE_HOST', $saved['environment_variables']);
        $this->assertSame('9443', $saved['environment_variables']['SERVER_PORT']);
        $this->assertSame('worker', $saved['environment_variables']['SERVER_SECURITY_MANAGER']);
        $this->assertArrayHasKey('common_environment_variables', FilamentDefenderDataHarness::loadForm($saved));
    }
}
