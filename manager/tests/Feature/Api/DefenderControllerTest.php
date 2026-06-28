<?php

namespace Tests\Feature\Api;

use App\Models\Key;
use App\Models\User;
use App\Services\ApiAuthentication;

class DefenderControllerTest extends ApiTestCase
{
    public function test_defenders_payload_endpoint_requires_permission_and_masks_sensitive_defaults(): void
    {
        config()->set('database.connections.mysql.host', 'secret-db-host');
        config()->set('database.connections.mysql.database', 'secret-db-name');
        config()->set('database.connections.mysql.username', 'secret-db-user');
        config()->set('database.connections.mysql.password', 'secret-db-pass');

        $response = $this->apiJson('GET', $this->apiRoute('defenders', 'payload'))
            ->assertOk();

        $variables = $response->json('store.body.environment_variables');

        $this->assertSame('<database-host>', $variables['DATABASE_HOST'] ?? null);
        $this->assertSame('<database-name>', $variables['DATABASE_NAME'] ?? null);
        $this->assertSame('<database-user>', $variables['DATABASE_USER'] ?? null);
        $this->assertSame('<database-password>', $variables['DATABASE_PASS'] ?? null);
        $this->assertSame('<defender-password>', $variables['SERVER_SECURITY_PASSWORD'] ?? null);
        $this->assertStringNotContainsString('secret-db-pass', json_encode($response->json()));

        $regularPassword = 'regular-pass';
        $regularToken = 'regular-defender-payload-token';
        $regular = User::factory()->create([
            'email' => 'regular-defender-payload@example.com',
            'password' => $regularPassword,
            'is_root' => false,
            'is_verified' => true,
            'is_activated' => true,
        ]);
        Key::withoutEvents(fn () => Key::query()->create([
            'name' => 'regular-defender-payload-token',
            'token' => $regularToken,
            'is_reused' => true,
            'created_by' => $regular->id,
        ]));

        $this->withBasicAuth($regular->email, $regularPassword)
            ->withHeaders([
                'Accept' => 'application/json',
                ApiAuthentication::tokenKeyName() => $regularToken,
            ])
            ->getJson(route($this->apiRoute('defenders', 'payload')))
            ->assertForbidden();
    }

    public function test_defenders_api_crud_validation_and_put_patch_behavior(): void
    {
        $this->apiJson('GET', $this->apiRoute('defenders', 'index'))->assertOk();
        $this->apiJson('POST', $this->apiRoute('defenders', 'store'), [], [])->assertUnprocessable();

        $storePayload = [
            'name' => 'primary-defender',
            'proxy_port' => 9948,
            'environment_variables' => [
                'PROXY_BACKEND_URL' => 'http://localhost',
                'PROXY_PORT' => '9948',
            ],
            'description' => 'Defender record.',
        ];

        $storeResponse = $this->apiJson('POST', $this->apiRoute('defenders', 'store'), [], $storePayload)
            ->assertCreated();

        $defenderId = (string) $storeResponse->json('id');

        $this->apiJson('GET', $this->apiRoute('defenders', 'show'), ['defender' => $defenderId])
            ->assertOk()
            ->assertJsonPath('id', $defenderId);

        $this->apiJson('PATCH', $this->apiRoute('defenders', 'update'), ['defender' => $defenderId], [
            'description' => 'Patched defender.',
        ])->assertOk()->assertJsonPath('description', 'Patched defender.');

        $this->apiJson('PUT', $this->apiRoute('defenders', 'update'), ['defender' => $defenderId], [
            'description' => 'Only description',
        ])->assertUnprocessable();

        $putPayload = [
            'name' => 'secondary-defender',
            'proxy_port' => 9950,
            'environment_variables' => [
                'PROXY_BACKEND_URL' => 'https://example.com',
                'PROXY_PORT' => '9950',
            ],
            'description' => 'Replaced defender.',
        ];

        $this->apiJson('PUT', $this->apiRoute('defenders', 'update'), ['defender' => $defenderId], $putPayload)
            ->assertOk()
            ->assertJsonPath('name', 'secondary-defender');

        $this->apiJson('DELETE', $this->apiRoute('defenders', 'destroy'), ['defender' => $defenderId])->assertNoContent();
        $this->assertDatabaseMissing('defenders', ['id' => $defenderId]);
    }
}
