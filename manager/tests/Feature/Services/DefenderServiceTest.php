<?php

namespace Tests\Feature\Services;

use App\Enums\Defender\DeploymentStatus;
use App\Models\Defender;
use App\Models\User;
use App\Services\Defender as DefenderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\Support\DomainTestHelpers;
use Tests\Support\RawDefenderForAuthorization;
use Tests\TestCase;

class DefenderServiceTest extends TestCase
{
    use DomainTestHelpers;
    use RefreshDatabase;

    public function test_defender_service_sends_policy_and_decision_requests_from_environment_variables(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $defender = $this->defender('edge', DeploymentStatus::Successful->value, [
            'SERVER_HTTPS_ENABLE' => 'false',
            'SERVER_PORT' => '8080',
            'SERVER_CONTROLLER_PATH_PREFIX' => 'control',
            'SERVER_CONTROLLER_PATH_PRINCIPLES' => 'policies',
            'SERVER_CONTROLLER_PATH_DECISIONS' => 'decisions',
            'SERVER_CONTROLLER_AUTHORIZATION_EMAIL' => 'X-Actor',
            'SERVER_SECURITY_USERNAME' => '',
            'SERVER_SECURITY_PASSWORD' => '',
        ]);

        DefenderService::apply($defender, ['p1', 'p1', ' '], requesterEmail: 'operator@example.com');
        DefenderService::revoke($defender, ['p1'], requesterEmail: 'operator@example.com');
        DefenderService::implement($defender, ['d1'], requesterEmail: 'operator@example.com');
        DefenderService::suspend($defender, ['d1'], requesterEmail: 'operator@example.com');

        Http::assertSent(fn (ClientRequest $request) => $request->method() === 'PUT'
            && $request->url() === 'http://edge:8080/control/policies'
            && $request['principle_ids'] === ['p1']
            && $request->hasHeader('X-Actor', 'operator@example.com'));
        Http::assertSent(fn (ClientRequest $request) => $request->method() === 'DELETE'
            && $request->url() === 'http://edge:8080/control/policies');
        Http::assertSent(fn (ClientRequest $request) => $request->method() === 'PUT'
            && $request->url() === 'http://edge:8080/control/decisions'
            && $request['decision_ids'] === ['d1']);
        Http::assertSent(fn (ClientRequest $request) => $request->method() === 'DELETE'
            && $request->url() === 'http://edge:8080/control/decisions');
    }

    public function test_defender_service_handles_tls_and_environment_edge_branches(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        config()->set('customization.backend.apis.defender.tls.skip_verify', true);
        $emptyNameDefender = $this->defender('', DeploymentStatus::Successful->value, [
            'SERVER_HTTPS_ENABLE' => 'false',
            'SERVER_SECURITY_USERNAME' => '',
            'SERVER_SECURITY_PASSWORD' => '',
        ]);
        DefenderService::apply($emptyNameDefender, ['p1']);
        Http::assertSent(fn (ClientRequest $request) => $request->url() === 'http://defender:9947/api/v1/principles');

        config()->set('customization.backend.apis.defender.tls.skip_verify', false);
        DefenderService::apply($emptyNameDefender, ['p2']);
        config()->set('customization.backend.apis.defender.tls.directory', '');
        $listEnvironmentDefender = Defender::query()->create([
            'name' => 'list-env',
            'proxy_port' => 9948,
            'deployment_status' => DeploymentStatus::Successful,
            'environment_variables' => [
                ['key' => 'SERVER_HTTPS_ENABLE', 'value' => 'true'],
                ['key' => 'SERVER_PORT', 'value' => ['bad']],
                ['key' => 'SERVER_CONTROLLER_AUTHORIZATION_EMAIL', 'value' => ''],
                ['key' => 'SERVER_SECURITY_USERNAME', 'value' => ''],
                ['key' => 'SERVER_SECURITY_PASSWORD', 'value' => ''],
            ],
        ]);
        DefenderService::revoke($listEnvironmentDefender, ['p1'], requesterEmail: 'operator@example.com');

        $invalidPortDefender = Defender::query()->create([
            'name' => 'invalid-port',
            'proxy_port' => 9948,
            'deployment_status' => DeploymentStatus::Successful,
            'environment_variables' => [
                ['key' => 'SERVER_HTTPS_ENABLE', 'value' => 'false'],
                ['key' => 'SERVER_PORT', 'value' => '70000'],
                ['key' => 'SERVER_SECURITY_USERNAME', 'value' => ''],
                ['key' => 'SERVER_SECURITY_PASSWORD', 'value' => ''],
            ],
        ]);
        $requestUser = User::factory()->create(['is_root' => true, 'is_verified' => true, 'is_activated' => true]);
        $this->actingAs($requestUser);
        DefenderService::apply($invalidPortDefender, ['p3']);

        $arrayValueDefender = Defender::query()->create([
            'name' => 'array-value',
            'proxy_port' => 9948,
            'deployment_status' => DeploymentStatus::Successful,
            'environment_variables' => [
                ['key' => 'SERVER_HTTPS_ENABLE', 'value' => 'true'],
                ['key' => 'SERVER_CONTROLLER_PATH_PREFIX', 'value' => ['bad']],
                ['key' => 'SERVER_CONTROLLER_AUTHORIZATION_EMAIL', 'value' => ''],
                ['key' => 'SERVER_SECURITY_USERNAME', 'value' => ''],
                ['key' => 'SERVER_SECURITY_PASSWORD', 'value' => ''],
            ],
        ]);
        DefenderService::suspend($arrayValueDefender, ['d1']);

        $noEnvironmentDefender = new RawDefenderForAuthorization();
        $noEnvironmentDefender->setRawAttributes([
            'name' => 'no-env',
            'environment_variables' => 'not-an-array',
        ], true);
        DefenderService::implement($noEnvironmentDefender, ['d1'], ['custom' => true]);
    }
}
