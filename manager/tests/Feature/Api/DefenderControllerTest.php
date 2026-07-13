<?php

namespace Tests\Feature\Api;

use App\Enums\Defender\DeploymentStatus;
use App\Jobs\DefenderDeployment;
use App\Models\Guard;
use App\Models\Key;
use App\Models\User;
use App\Services\ApiAuthentication;
use Illuminate\Support\Facades\Bus;
use Tests\Support\ApiRelationTestHelpers;

class DefenderControllerTest extends ApiTestCase
{
    use ApiRelationTestHelpers;

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
        $this->assertStringNotContainsString('secret-db-pass', json_encode($response->json(), JSON_THROW_ON_ERROR));

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

    public function test_guarded_defender_action_requests_require_active_matching_guard(): void
    {
        Bus::fake();

        $defender = $this->apiDefender('guarded-deploy', DeploymentStatus::Failed->value);
        $guard = Guard::query()->create([
            'name' => 'api-guard',
            'expired_at' => now()->addHour(),
        ]);
        $guard->defenders()->attach($defender->id);

        $this->apiJson('POST', $this->apiRoute('defenders', 'deploy'), ['defender' => $defender->id])
            ->assertForbidden();
        Bus::assertNotDispatched(DefenderDeployment::class);

        $guard->users()->attach($this->user->id);

        $this->apiJson('POST', $this->apiRoute('defenders', 'deploy'), ['defender' => $defender->id])
            ->assertOk();
        Bus::assertDispatched(DefenderDeployment::class);

        Bus::fake();
        $defender->forceFill(['deployment_status' => DeploymentStatus::Failed])->save();
        $guard->forceFill(['expired_at' => now()->subMinute()])->save();

        $this->apiJson('POST', $this->apiRoute('defenders', 'deploy'), ['defender' => $defender->id])
            ->assertForbidden();
        Bus::assertNotDispatched(DefenderDeployment::class);

        Bus::fake();
        $ownedDefender = $this->apiDefender('owned-guarded-deploy', DeploymentStatus::Failed->value);
        $ownedDefender->forceFill(['created_by' => $this->user->id])->saveQuietly();
        $guard->defenders()->attach($ownedDefender->id);

        $this->apiJson('POST', $this->apiRoute('defenders', 'deploy'), ['defender' => $ownedDefender->id])
            ->assertOk();
        Bus::assertDispatched(DefenderDeployment::class);
    }

    public function test_defenders_index_scopes_guarded_records_to_owner_or_active_guard_users(): void
    {
        $publicDefender = $this->apiDefender('public-index', DeploymentStatus::Failed->value);
        $guardedDefender = $this->apiDefender('guarded-index', DeploymentStatus::Failed->value);
        $ownedDefender = $this->apiDefender('owned-index', DeploymentStatus::Failed->value);
        $ownedDefender->forceFill(['created_by' => $this->user->id])->saveQuietly();

        $guard = Guard::query()->create([
            'name' => 'api-index-guard',
            'expired_at' => now()->addHour(),
        ]);
        $guard->defenders()->attach([$guardedDefender->id, $ownedDefender->id]);

        $this->apiJson('GET', $this->apiRoute('defenders', 'index'))
            ->assertOk()
            ->assertJsonFragment(['id' => $publicDefender->id])
            ->assertJsonFragment(['id' => $ownedDefender->id])
            ->assertJsonMissing(['id' => $guardedDefender->id]);

        $guard->users()->attach($this->user->id);

        $this->apiJson('GET', $this->apiRoute('defenders', 'index'))
            ->assertOk()
            ->assertJsonFragment(['id' => $guardedDefender->id]);
    }
}
