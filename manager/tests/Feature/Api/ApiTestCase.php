<?php

namespace Tests\Feature\Api;

use App\Models\Key;
use App\Models\User;
use App\Services\ApiAuthentication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected string $password = 'secret-pass';

    protected string $apiToken = 'test-api-token';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->user = User::factory()->create([
            'email' => 'root@example.com',
            'password' => $this->password,
            'is_root' => true,
            'is_verified' => true,
            'is_activated' => true,
        ]);

        Key::withoutEvents(fn () => Key::query()->create([
            'name' => 'root-token',
            'token' => $this->apiToken,
            'is_reused' => true,
            'created_by' => $this->user->id,
        ]));
    }

    protected function assertApiAuthRequired(string $routeName, array $routeParameters = []): void
    {
        $url = route($routeName, $routeParameters);

        $this->getJson($url)->assertUnauthorized();

        $this->withBasicAuth($this->user->email, $this->password)
            ->getJson($url)
            ->assertUnauthorized();

        $this->withBasicAuth($this->user->email, $this->password)
            ->withHeaders([ApiAuthentication::tokenKeyName() => 'invalid-token'])
            ->getJson($url)
            ->assertUnauthorized();
    }

    protected function apiRoute(string $resource, string $action): string
    {
        return "defly_manager.api.{$resource}.{$action}";
    }

    protected function apiHeaders(array $headers = []): array
    {
        return array_merge([
            'Accept' => 'application/json',
            ApiAuthentication::tokenKeyName() => $this->apiToken,
        ], $headers);
    }

    protected function apiJson(
        string $method,
        string $routeName,
        array $routeParameters = [],
        array $payload = []
    ): TestResponse {
        return $this->apiJsonToUrl($method, route($routeName, $routeParameters), $payload);
    }

    protected function apiJsonToUrl(string $method, string $url, array $payload = []): TestResponse
    {
        return $this->withBasicAuth($this->user->email, $this->password)
            ->withHeaders($this->apiHeaders())
            ->json($method, $url, $payload);
    }

    protected function apiForm(
        string $method,
        string $routeName,
        array $routeParameters = [],
        array $payload = []
    ): TestResponse {
        $request = $this->withBasicAuth($this->user->email, $this->password)
            ->withHeaders($this->apiHeaders());

        $url = route($routeName, $routeParameters);

        return match (strtoupper($method)) {
            'POST' => $request->post($url, $payload),
            'PUT' => $request->put($url, $payload),
            'PATCH' => $request->patch($url, $payload),
            default => throw new \InvalidArgumentException("Unsupported form method: {$method}"),
        };
    }

    protected function fakeTextFile(string $name, string $content): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, $content);
    }
}
