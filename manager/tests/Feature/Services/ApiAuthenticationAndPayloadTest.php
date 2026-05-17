<?php

namespace Tests\Feature\Services;

use App\Services\ApiAuthentication;
use App\Services\ApiPayload;
use Illuminate\Http\Request;
use Tests\TestCase;

class ApiAuthenticationAndPayloadTest extends TestCase
{
    public function test_api_token_can_be_read_from_header_or_body(): void
    {
        config()->set('customization.backend.apis.authentication.token_location', 'header');
        config()->set('customization.backend.apis.authentication.token_key_name', 'X-Test-Token');

        $request = Request::create('/api', 'POST', [], [], [], ['HTTP_X_TEST_TOKEN' => 'header-token']);
        $this->assertSame('header-token', ApiAuthentication::tokenFrom($request));
        $this->assertArrayHasKey('X-Test-Token', ApiAuthentication::headers());
        $this->assertSame(['name' => 'example'], ApiAuthentication::body(['name' => 'example']));

        config()->set('customization.backend.apis.authentication.token_location', 'body');

        $request = Request::create('/api', 'POST', ['X-Test-Token' => 'body-token']);
        $this->assertSame('body-token', ApiAuthentication::tokenFrom($request));
        $this->assertArrayNotHasKey('X-Test-Token', ApiAuthentication::headers());
        $this->assertSame(ApiAuthentication::TOKEN_PLACEHOLDER, ApiAuthentication::body([])['X-Test-Token']);
    }

    public function test_api_payload_builds_authenticated_resource_urls(): void
    {
        config()->set('customization.backend.apis.authentication.token_location', 'body');
        config()->set('customization.backend.apis.authentication.token_key_name', 'X-Test-Token');

        $payload = ApiPayload::resource('actions', [
            'store' => ['method' => 'post', 'path' => 'custom', 'body' => ['name' => 'action']],
        ]);

        $this->assertSame('POST', $payload['store']['method']);
        $this->assertStringEndsWith('/api/v1/actions/custom', $payload['store']['url']);
        $this->assertSame(ApiAuthentication::TOKEN_PLACEHOLDER, $payload['store']['body']['X-Test-Token']);
    }
}
