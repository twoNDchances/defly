<?php

namespace Tests\Feature\Jobs;

use App\Enums\Defender\DeploymentStatus;
use App\Jobs\DefenderDeployment;
use App\Models\Guard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\Support\DomainTestHelpers;
use Tests\TestCase;

class DefenderDeploymentJobTest extends TestCase
{
    use DomainTestHelpers;
    use RefreshDatabase;

    public function test_deployment_job_updates_status_for_success_failure_and_cancel(): void
    {
        Http::fakeSequence()
            ->push(['deployment' => 'ready'], 200)
            ->push('not available', 503)
            ->push(['cancelled' => true], 200);

        $deploying = $this->defender('deploying', DeploymentStatus::Pending->value);
        (new DefenderDeployment($deploying->id, DefenderDeployment::ACTION_DEPLOY))->handle();
        $this->assertSame(DeploymentStatus::Successful, $deploying->refresh()->deployment_status);
        $this->assertSame('ready', $deploying->refresh()->deployment_details['deployment']);

        $failing = $this->defender('failing', DeploymentStatus::Pending->value);
        (new DefenderDeployment($failing->id, DefenderDeployment::ACTION_DEPLOY))->handle();
        $this->assertSame(DeploymentStatus::Failed, $failing->refresh()->deployment_status);
        $this->assertSame(503, $failing->refresh()->deployment_details['status']);

        $cancelling = $this->defender('cancelling', DeploymentStatus::Successful->value);
        (new DefenderDeployment($cancelling->id, DefenderDeployment::ACTION_CANCEL))->handle();
        $this->assertNull($cancelling->refresh()->deployment_status);
    }

    public function test_deployment_job_skips_missing_records_and_idle_states(): void
    {
        (new DefenderDeployment((string) Str::uuid()))->handle();

        $idle = $this->defender('idle', null);
        (new DefenderDeployment($idle->id, DefenderDeployment::ACTION_DEPLOY))->handle();
        $this->assertNull($idle->refresh()->deployment_status);

        $cancelSkipped = $this->defender('cancel-skipped', DeploymentStatus::Failed->value);
        (new DefenderDeployment($cancelSkipped->id, DefenderDeployment::ACTION_CANCEL))->handle();
        $this->assertSame(DeploymentStatus::Failed, $cancelSkipped->refresh()->deployment_status);
    }

    public function test_deployment_job_records_cancel_failures_exceptions_and_default_actions(): void
    {
        $log = Log::spy();

        Http::fake(['*' => Http::response('cancel failed', 503)]);
        $cancelFailure = $this->defender('cancel-failure', DeploymentStatus::Successful->value);
        (new DefenderDeployment($cancelFailure->id, DefenderDeployment::ACTION_CANCEL))->handle();
        $this->assertSame(DeploymentStatus::Failed, $cancelFailure->refresh()->deployment_status);
        $this->assertSame(503, $cancelFailure->refresh()->deployment_details['status']);
        $this->assertSame('cancel failed', $cancelFailure->refresh()->deployment_details['response']['body']);

        Http::fake(['*' => fn () => throw new \RuntimeException('orchestrator down')]);
        $deployException = $this->defender('deploy-exception', DeploymentStatus::Pending->value);
        (new DefenderDeployment($deployException->id, DefenderDeployment::ACTION_DEPLOY))->handle();
        $this->assertSame(DeploymentStatus::Failed, $deployException->refresh()->deployment_status);
        $this->assertSame('orchestrator down', $deployException->refresh()->deployment_details['message']);

        Http::fake(['*' => fn () => throw new \RuntimeException('cancel down')]);
        $cancelException = $this->defender('cancel-exception', DeploymentStatus::Successful->value);
        (new DefenderDeployment($cancelException->id, DefenderDeployment::ACTION_CANCEL))->handle();
        $this->assertSame(DeploymentStatus::Failed, $cancelException->refresh()->deployment_status);
        $this->assertContains($cancelException->refresh()->deployment_details['message'], ['orchestrator down', 'cancel down']);

        Http::fake(['*' => Http::response(['ok' => true], 200)]);
        $defaultAction = $this->defender('default-action', DeploymentStatus::Pending->value);
        $defaultDeploymentJob = new DefenderDeployment($defaultAction->id, 'unsupported');
        $this->assertSame(DefenderDeployment::ACTION_DEPLOY, $this->invokeJob($defaultDeploymentJob, 'normalizedAction'));
        $defaultDeploymentJob->handle();
        $this->assertNotSame(DeploymentStatus::Pending, $defaultAction->refresh()->deployment_status);

        $log->shouldHaveReceived('error');
    }

    public function test_deployment_job_marks_guarded_defender_failed_when_requester_guard_is_expired(): void
    {
        Http::fake(['*' => Http::response(['deployment' => 'ready'], 200)]);

        $user = User::factory()->create([
            'email' => 'expired-deployer@example.com',
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $defender = $this->defender('expired-deploy', DeploymentStatus::Pending->value);
        $guard = Guard::query()->create([
            'name' => 'expired-deployment-guard',
            'expired_at' => now()->subMinute(),
        ]);
        $guard->users()->attach($user->id);
        $guard->defenders()->attach($defender->id);

        (new DefenderDeployment(
            $defender->id,
            DefenderDeployment::ACTION_DEPLOY,
            $user->email,
        ))->handle();

        $this->assertSame(DeploymentStatus::Failed, $defender->refresh()->deployment_status);
        $this->assertSame(__('notifications.defender.guard.denied'), $defender->deployment_details['detail']);
        Http::assertNothingSent();
    }
}
