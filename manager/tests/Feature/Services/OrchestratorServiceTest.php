<?php

namespace Tests\Feature\Services;

use App\Services\Orchestrator;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrchestratorServiceTest extends TestCase
{
    public function test_orchestrator_service_sends_deploy_follow_and_cancel_requests(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        Orchestrator::deploy('abc-123', requesterEmail: 'operator@example.com');
        Orchestrator::follow('abc-123', ['tail' => '1'], requesterEmail: 'operator@example.com');
        Orchestrator::cancel('abc-123', requesterEmail: 'operator@example.com');

        config()->set('customization.backend.apis.orchestrator.tls.skip_verify', true);
        Orchestrator::deploy('skip-verify');
        config()->set('customization.backend.apis.orchestrator.tls.skip_verify', false);
        config()->set('customization.backend.apis.orchestrator.tls.cert_file', '');
        config()->set('customization.backend.apis.orchestrator.headers.email_header_key', '');
        Orchestrator::follow('empty-header');
        config()->set('customization.backend.apis.orchestrator.headers.email_header_key', 'X-Executor');

        Http::assertSent(fn (ClientRequest $request) => $request->method() === 'POST'
            && str_ends_with($request->url(), '/api/v1/deployments/abc-123')
            && $request->hasHeader('X-Executor', 'operator@example.com'));
        Http::assertSent(fn (ClientRequest $request) => $request->method() === 'GET'
            && str_contains($request->url(), '/api/v1/deployments/abc-123')
            && str_contains($request->url(), 'tail=1'));
        Http::assertSent(fn (ClientRequest $request) => $request->method() === 'DELETE'
            && str_ends_with($request->url(), '/api/v1/deployments/abc-123'));
    }
}
