<?php

namespace Tests\Feature\Jobs;

use App\Enums\Defender\DeploymentStatus;
use App\Jobs\DefenderCommunication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\Support\DomainTestHelpers;
use Tests\TestCase;

class DefenderCommunicationJobTest extends TestCase
{
    use DomainTestHelpers;
    use RefreshDatabase;

    public function test_communication_job_updates_pivots_and_response_details(): void
    {
        Log::spy();

        $defender = $this->defender('communicator', DeploymentStatus::Successful->value, [
            'SERVER_HTTPS_ENABLE' => 'false',
            'SERVER_PORT' => '9947',
            'SERVER_CONTROLLER_PATH_PREFIX' => 'api/v1',
        ]);
        $principle = $this->principle();
        $decision = $this->decision();
        $defender->principles()->attach($principle->id, ['order' => 1, 'is_applied' => false]);
        $defender->decisions()->attach($decision->id, ['order' => 1, 'is_implemented' => false]);

        Http::fakeSequence()
            ->push(['ok' => true], 200)
            ->push(['ok' => true], 200)
            ->push(['ok' => false], 500)
            ->push('decision failed', 500);

        (new DefenderCommunication($defender->id, [$principle->id], DefenderCommunication::ACTION_APPLY))->handle();
        $this->assertTrue((bool) $defender->principles()->whereKey($principle->id)->first()->pivot->is_applied);
        $this->assertSame('successful', $defender->fresh()->last_response_details['principle']['status']);

        (new DefenderCommunication($defender->id, [$decision->id], DefenderCommunication::ACTION_IMPLEMENT))->handle();
        $this->assertTrue((bool) $defender->decisions()->whereKey($decision->id)->first()->pivot->is_implemented);
        $this->assertSame('successful', $defender->fresh()->last_response_details['decision']['status']);

        (new DefenderCommunication($defender->id, [$principle->id], DefenderCommunication::ACTION_REVOKE))->handle();
        $this->assertSame('failed', $defender->fresh()->last_response_details['principle']['status']);

        (new DefenderCommunication($defender->id, [$decision->id], DefenderCommunication::ACTION_IMPLEMENT))->handle();
        $this->assertSame('decision failed', $defender->fresh()->last_response_details['decision']['response']['body']);

        Log::shouldHaveReceived('warning')
            ->with('Defender communication request failed.', \Mockery::type('array'))
            ->twice();
    }

    public function test_communication_job_skips_missing_unavailable_and_empty_action_inputs(): void
    {
        (new DefenderCommunication((string) Str::uuid(), ['missing'], DefenderCommunication::ACTION_APPLY))->handle();

        $notDeployed = $this->defender('not-deployed', DeploymentStatus::Failed->value);
        (new DefenderCommunication($notDeployed->id, ['anything'], DefenderCommunication::ACTION_APPLY))->handle();
        $this->assertNull($notDeployed->fresh()->last_response_details);

        $deployed = $this->defender('deployed-empty', DeploymentStatus::Successful->value);
        (new DefenderCommunication($deployed->id, ['', '  '], 'unknown-action'))->handle();
        (new DefenderCommunication($deployed->id, [(string) Str::uuid()], DefenderCommunication::ACTION_APPLY))->handle();
        (new DefenderCommunication($deployed->id, [(string) Str::uuid()], DefenderCommunication::ACTION_IMPLEMENT))->handle();
        $this->assertNull($deployed->fresh()->last_response_details);
    }

    public function test_communication_job_handles_revoke_suspend_failures_and_exceptions(): void
    {
        Log::spy();

        $communicator = $this->defender('communicator-extra', DeploymentStatus::Successful->value, [
            'SERVER_HTTPS_ENABLE' => 'false',
            'SERVER_PORT' => '9947',
            'SERVER_CONTROLLER_PATH_PREFIX' => 'api/v1',
        ]);
        $principle = $this->principle();
        $decision = $this->decision();
        $communicator->principles()->attach($principle->id, ['order' => 1, 'is_applied' => true]);
        $communicator->decisions()->attach($decision->id, ['order' => 1, 'is_implemented' => true]);

        Http::fakeSequence()
            ->push(['ok' => true], 200)
            ->push(['ok' => true], 200)
            ->push(['ok' => false], 500);
        (new DefenderCommunication($communicator->id, [$principle->id], DefenderCommunication::ACTION_REVOKE))->handle();
        $this->assertNotNull($communicator->fresh()->last_response_details);
        (new DefenderCommunication($communicator->id, [$decision->id], DefenderCommunication::ACTION_SUSPEND))->handle();
        $this->assertNotNull($communicator->fresh()->last_response_details);

        (new DefenderCommunication($communicator->id, [$decision->id], DefenderCommunication::ACTION_IMPLEMENT))->handle();
        $this->assertSame('failed', $communicator->fresh()->last_response_details['decision']['status']);

        Http::fake(fn () => throw new \RuntimeException('defender offline'));
        (new DefenderCommunication($communicator->id, [$principle->id], DefenderCommunication::ACTION_APPLY))->handle();
        $this->assertSame('failed', $communicator->fresh()->last_response_details['principle']['status']);
        $this->assertArrayHasKey('message', $communicator->fresh()->last_response_details['principle']);

        Log::shouldHaveReceived('warning')
            ->with('Defender communication request failed.', \Mockery::type('array'))
            ->once();
        Log::shouldHaveReceived('error')->once();
    }
}
