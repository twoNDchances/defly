<?php

namespace Tests\Feature\Jobs;

use App\Enums\Defender\DeploymentStatus;
use App\Jobs\DefenderCommunication;
use App\Models\Guard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mockery\VerificationDirector;
use Tests\Support\DomainTestHelpers;
use Tests\TestCase;

class DefenderCommunicationJobTest extends TestCase
{
    use DomainTestHelpers;
    use RefreshDatabase;

    public function test_communication_job_updates_pivots_and_response_details(): void
    {
        $log = Log::spy();

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
        $this->assertSame('successful', $defender->refresh()->last_response_details['principle']['status']);

        (new DefenderCommunication($defender->id, [$decision->id], DefenderCommunication::ACTION_IMPLEMENT))->handle();
        $this->assertTrue((bool) $defender->decisions()->whereKey($decision->id)->first()->pivot->is_implemented);
        $this->assertSame('successful', $defender->refresh()->last_response_details['decision']['status']);

        (new DefenderCommunication($defender->id, [$principle->id], DefenderCommunication::ACTION_REVOKE))->handle();
        $this->assertSame('failed', $defender->refresh()->last_response_details['principle']['status']);

        (new DefenderCommunication($defender->id, [$decision->id], DefenderCommunication::ACTION_IMPLEMENT))->handle();
        $this->assertSame('decision failed', $defender->refresh()->last_response_details['decision']['response']['body']);

        $warningExpectation = $log->shouldHaveReceived('warning');
        $this->assertInstanceOf(VerificationDirector::class, $warningExpectation);
        $warningExpectation
            ->with('Defender communication request failed.', \Mockery::type('array'))
            ->twice();
    }

    public function test_communication_job_skips_missing_unavailable_and_empty_action_inputs(): void
    {
        (new DefenderCommunication((string) Str::uuid(), ['missing'], DefenderCommunication::ACTION_APPLY))->handle();

        $notDeployed = $this->defender('not-deployed', DeploymentStatus::Failed->value);
        (new DefenderCommunication($notDeployed->id, ['anything'], DefenderCommunication::ACTION_APPLY))->handle();
        $this->assertNull($notDeployed->refresh()->last_response_details);

        $deployed = $this->defender('deployed-empty', DeploymentStatus::Successful->value);
        (new DefenderCommunication($deployed->id, ['', '  '], 'unknown-action'))->handle();
        (new DefenderCommunication($deployed->id, [(string) Str::uuid()], DefenderCommunication::ACTION_APPLY))->handle();
        (new DefenderCommunication($deployed->id, [(string) Str::uuid()], DefenderCommunication::ACTION_IMPLEMENT))->handle();
        $this->assertNull($deployed->refresh()->last_response_details);
    }

    public function test_communication_job_handles_revoke_suspend_failures_and_exceptions(): void
    {
        $log = Log::spy();

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
        $this->assertNotNull($communicator->refresh()->last_response_details);
        (new DefenderCommunication($communicator->id, [$decision->id], DefenderCommunication::ACTION_SUSPEND))->handle();
        $this->assertNotNull($communicator->refresh()->last_response_details);

        (new DefenderCommunication($communicator->id, [$decision->id], DefenderCommunication::ACTION_IMPLEMENT))->handle();
        $this->assertSame('failed', $communicator->refresh()->last_response_details['decision']['status']);

        Http::fake(fn () => throw new \RuntimeException('defender offline'));
        (new DefenderCommunication($communicator->id, [$principle->id], DefenderCommunication::ACTION_APPLY))->handle();
        $this->assertSame('failed', $communicator->refresh()->last_response_details['principle']['status']);
        $this->assertArrayHasKey('message', $communicator->refresh()->last_response_details['principle']);

        $warningExpectation = $log->shouldHaveReceived('warning');
        $this->assertInstanceOf(VerificationDirector::class, $warningExpectation);
        $warningExpectation
            ->with('Defender communication request failed.', \Mockery::type('array'))
            ->once();
        $errorExpectation = $log->shouldHaveReceived('error');
        $this->assertInstanceOf(VerificationDirector::class, $errorExpectation);
        $errorExpectation->once();
    }

    public function test_communication_job_skips_guarded_defender_when_requester_guard_is_expired(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $user = User::factory()->create([
            'email' => 'expired-communicator@example.com',
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $defender = $this->defender('expired-communicator', DeploymentStatus::Successful->value);
        $principle = $this->principle();
        $defender->principles()->attach($principle->id, ['order' => 1, 'is_applied' => false]);
        $guard = Guard::query()->create([
            'name' => 'expired-communication-guard',
            'expired_at' => now()->subMinute(),
        ]);
        $guard->users()->attach($user->id);
        $guard->defenders()->attach($defender->id);

        (new DefenderCommunication(
            $defender->id,
            [$principle->id],
            DefenderCommunication::ACTION_APPLY,
            $user->email,
        ))->handle();

        $this->assertFalse((bool) $defender->principles()->whereKey($principle->id)->first()->pivot->is_applied);
        Http::assertNothingSent();
    }
}
