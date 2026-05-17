<?php

namespace Tests\Feature\Services;

use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\Support\ConnectorHarness;
use Tests\TestCase;

class ConnectorServiceTest extends TestCase
{
    public function test_connector_builds_base_uri_headers_methods_and_query_requests(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        ConnectorHarness::configure('https://connector.test', 'api', 'user', 'pass', ['X-Base' => '1']);
        ConnectorHarness::send('items', 'patch', ['ok' => true]);
        Http::assertSent(fn (ClientRequest $request) => $request->method() === 'PATCH'
            && $request->url() === 'https://connector.test/api/items'
            && $request->hasHeader('X-Base', '1'));
        $this->assertSame('https://connector.test/api', ConnectorHarness::baseUriPublic());

        ConnectorHarness::configure('https://connector.test', '', null, null);
        ConnectorHarness::send('/items', 'get', ['page' => 1]);
        Http::assertSent(fn (ClientRequest $request) => $request->method() === 'GET'
            && $request->url() === 'https://connector.test/items?page=1');
    }
}
