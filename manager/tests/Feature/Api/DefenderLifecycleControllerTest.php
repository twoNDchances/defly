<?php

namespace Tests\Feature\Api;

use App\Enums\Defender\DeploymentStatus;
use App\Http\Controllers\DefenderController;
use App\Http\Requests\DefenderActionRequest;
use App\Jobs\DefenderDeployment;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Tests\Support\ApiRelationTestHelpers;

class DefenderLifecycleControllerTest extends ApiTestCase
{
    use ApiRelationTestHelpers;

    public function test_defender_deploy_cancel_and_follow_endpoints(): void
    {
        Bus::fake();
        config()->set('database.connections.mysql.host', 'manager-db');
        config()->set('database.connections.mysql.port', '3307');
        config()->set('database.connections.mysql.database', 'defly_runtime');
        config()->set('database.connections.mysql.username', 'defly_user');
        config()->set('database.connections.mysql.password', 'defly_secret');
        Http::fake([
            'orchestrator:8000/api/v1/deployments/*' => Http::response(['state' => 'running'], 200),
        ]);

        $pendingDefender = $this->apiDefender('pending', null);

        $this->apiJson('POST', $this->apiRoute('defenders', 'deploy'), ['defender' => $pendingDefender->id])
            ->assertOk()
            ->assertJsonPath('deployment_status', DeploymentStatus::Pending->value);

        Bus::assertDispatched(DefenderDeployment::class, fn (DefenderDeployment $job) => $job->action === DefenderDeployment::ACTION_DEPLOY);
        $pendingVariables = $pendingDefender->refresh()->environment_variables;
        $this->assertSame('manager-db', $pendingVariables['DATABASE_HOST'] ?? null);
        $this->assertSame('3307', $pendingVariables['DATABASE_PORT'] ?? null);
        $this->assertSame('defly_runtime', $pendingVariables['DATABASE_NAME'] ?? null);
        $this->assertSame('defly_user', $pendingVariables['DATABASE_USER'] ?? null);
        $this->assertSame('defly_secret', $pendingVariables['DATABASE_PASS'] ?? null);
        $this->assertSame('http://localhost', $pendingVariables['PROXY_BACKEND_URL'] ?? null);

        $successfulDefender = $this->apiDefender('successful', DeploymentStatus::Successful->value);

        $this->apiJson('POST', $this->apiRoute('defenders', 'cancel'), ['defender' => $successfulDefender->id])
            ->assertOk()
            ->assertJsonPath('deployment_status', DeploymentStatus::Pending->value);

        Bus::assertDispatched(DefenderDeployment::class, fn (DefenderDeployment $job) => $job->action === DefenderDeployment::ACTION_CANCEL);

        $this->apiJson('POST', $this->apiRoute('defenders', 'follow'), ['defender' => $successfulDefender->id])
            ->assertOk()
            ->assertJsonStructure(['log']);
    }

    public function test_defender_action_endpoints_return_current_records_when_not_actionable(): void
    {
        Bus::fake();

        $controller = new DefenderController;
        $request = DefenderActionRequest::create('/defenders/action', 'POST');
        $pendingDefender = $this->apiDefender('already-pending', DeploymentStatus::Pending->value);
        $this->assertSame(200, $controller->deploy($request, $pendingDefender)->getStatusCode());
        $this->assertSame(DeploymentStatus::Pending, $pendingDefender->refresh()->deployment_status);

        $failedDefender = $this->apiDefender('not-cancellable', DeploymentStatus::Failed->value);
        $this->assertSame(200, $controller->cancel($request, $failedDefender)->getStatusCode());
        $this->assertSame(DeploymentStatus::Failed, $failedDefender->refresh()->deployment_status);

        Http::fake(['*' => Http::response('plain-log', 200)]);
        $followResponse = $controller->follow($request, $failedDefender);
        $this->assertSame(200, $followResponse->getStatusCode());
        $this->assertArrayHasKey('log', $followResponse->getData(true));

        Bus::assertNotDispatched(DefenderDeployment::class, fn (DefenderDeployment $job) => $job->defenderId === $pendingDefender->id);
        Bus::assertNotDispatched(DefenderDeployment::class, fn (DefenderDeployment $job) => $job->defenderId === $failedDefender->id);
    }
}
