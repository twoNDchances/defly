<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\UserController;
use App\Http\Requests\UserRequest;
use App\Models\Group;
use App\Models\Key;
use App\Models\Label;
use App\Models\Permission;
use App\Models\User;
use App\Services\ApiAuthentication;
use Illuminate\Support\Str;

class UserControllerTest extends ApiTestCase
{
    public function test_users_api_requires_basic_auth_and_token(): void
    {
        $this->assertApiAuthRequired($this->apiRoute('users', 'index'));
    }

    public function test_api_token_middleware_rejects_head_and_wrong_basic_password(): void
    {
        $this->withBasicAuth($this->user->email, $this->password)
            ->withHeaders($this->apiHeaders())
            ->call('HEAD', route($this->apiRoute('users', 'index')))
            ->assertStatus(405);

        $this->withBasicAuth($this->user->email, 'wrong-password')
            ->withHeaders($this->apiHeaders())
            ->getJson(route($this->apiRoute('users', 'index')))
            ->assertUnauthorized();
    }

    public function test_users_payload_endpoint_reflects_root_fields(): void
    {
        $this->apiJson('GET', $this->apiRoute('users', 'payload'))
            ->assertOk()
            ->assertJsonPath('store.body.is_root', false)
            ->assertJsonPath('update.body.is_root', false);
    }

    public function test_users_api_crud_validation_and_put_patch_behavior(): void
    {
        User::factory()->create([
            'name' => 'Visible User',
            'email' => 'visible@example.com',
            'is_root' => false,
            'is_verified' => true,
            'is_activated' => true,
        ]);

        $this->apiJson('GET', $this->apiRoute('users', 'index'), [], ['per_page' => 1])
            ->assertOk()
            ->assertJsonPath('per_page', 1);

        $this->apiJson('POST', $this->apiRoute('users', 'store'), [], [])
            ->assertUnprocessable();

        $storeResponse = $this->apiJson('POST', $this->apiRoute('users', 'store'), [], [
            'name' => 'Managed User',
            'email' => 'managed@example.com',
            'password' => 'secret-pass',
            'is_activated' => true,
            'is_verified' => true,
            'is_root' => false,
        ])->assertCreated();

        $userId = (string) $storeResponse->json('id');

        $this->apiJson('GET', $this->apiRoute('users', 'show'), ['user' => $userId])
            ->assertOk()
            ->assertJsonPath('email', 'managed@example.com');

        $this->apiJson('PATCH', $this->apiRoute('users', 'update'), ['user' => $userId], [
            'name' => 'Patched User',
            'password' => '',
            'is_activated' => false,
        ])->assertOk()
            ->assertJsonPath('name', 'Patched User')
            ->assertJsonPath('is_activated', false);

        $this->apiJson('PUT', $this->apiRoute('users', 'update'), ['user' => $userId], [
            'name' => 'Missing Email',
        ])->assertUnprocessable();

        $this->apiJson('PUT', $this->apiRoute('users', 'update'), ['user' => $userId], [
            'name' => 'Replaced User',
            'email' => 'replaced@example.com',
            'password' => 'new-secret',
            'is_activated' => true,
            'is_root' => true,
        ])->assertOk()
            ->assertJsonPath('email', 'replaced@example.com')
            ->assertJsonPath('is_root', true);

        $this->apiJson('DELETE', $this->apiRoute('users', 'destroy'), ['user' => $userId])->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_users_relation_endpoints_attach_list_and_detach_records(): void
    {
        $target = User::factory()->create([
            'email' => 'relations-'.Str::lower(Str::random(6)).'@example.com',
            'is_root' => false,
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $permission = Permission::query()->create([
            'name' => 'permission-'.Str::lower(Str::random(6)),
            'applied_for' => 'Action',
            'action' => 'viewAny',
        ]);
        $group = Group::query()->create(['name' => 'group-'.Str::lower(Str::random(6))]);
        $label = Label::query()->create(['name' => 'label-'.Str::lower(Str::random(6)), 'color' => '#00aa88']);

        $this->apiJson('POST', $this->apiRoute('users.permissions', 'attach'), ['user' => $target->id], [
            'ids' => [$permission->id],
        ])->assertOk()->assertJsonFragment(['id' => $permission->id]);
        $this->apiJson('GET', $this->apiRoute('users.permissions', 'index'), ['user' => $target->id])
            ->assertOk()
            ->assertJsonFragment(['id' => $permission->id]);
        $this->apiJson('DELETE', $this->apiRoute('users.permissions', 'detach'), ['user' => $target->id], [
            'ids' => [$permission->id],
        ])->assertOk();

        $this->apiJson('POST', $this->apiRoute('users.groups', 'attach'), ['user' => $target->id], [
            'ids' => [$group->id],
        ])->assertOk()->assertJsonFragment(['id' => $group->id]);
        $this->apiJson('GET', $this->apiRoute('users.groups', 'index'), ['user' => $target->id])
            ->assertOk()
            ->assertJsonFragment(['id' => $group->id]);
        $this->apiJson('DELETE', $this->apiRoute('users.groups', 'detach'), ['user' => $target->id], [
            'ids' => [$group->id],
        ])->assertOk();

        $this->apiJson('POST', $this->apiRoute('users.labels', 'attach'), ['user' => $target->id], [
            'ids' => [$label->id],
        ])->assertOk()->assertJsonFragment(['id' => $label->id]);
        $this->apiJson('GET', $this->apiRoute('users.labels', 'index'), ['user' => $target->id])
            ->assertOk()
            ->assertJsonFragment(['id' => $label->id]);
        $this->apiJson('DELETE', $this->apiRoute('users.labels', 'detach'), ['user' => $target->id], [
            'ids' => [$label->id],
        ])->assertOk();
    }

    public function test_non_root_user_cannot_access_self_or_root_users(): void
    {
        $regularPassword = 'regular-pass';
        $regularToken = 'regular-token';
        $regular = User::factory()->create([
            'email' => 'regular@example.com',
            'password' => $regularPassword,
            'is_root' => false,
            'is_verified' => true,
            'is_activated' => true,
        ]);
        Key::withoutEvents(fn () => Key::query()->create([
            'name' => 'regular-token',
            'token' => $regularToken,
            'is_reused' => true,
            'created_by' => $regular->id,
        ]));

        $request = $this->withBasicAuth($regular->email, $regularPassword)
            ->withHeaders([
                'Accept' => 'application/json',
                ApiAuthentication::tokenKeyName() => $regularToken,
            ]);

        $request->getJson(route($this->apiRoute('users', 'show'), ['user' => $regular->id]))
            ->assertForbidden();
        $request->getJson(route($this->apiRoute('users', 'show'), ['user' => $this->user->id]))
            ->assertForbidden();
        $request->deleteJson(route($this->apiRoute('users', 'destroy'), ['user' => $this->user->id]))
            ->assertForbidden();
    }

    public function test_verification_route_marks_user_verified_and_rejects_invalid_tokens(): void
    {
        $user = User::withoutEvents(fn () => User::factory()->create([
            'email' => 'verify-'.Str::lower(Str::random(6)).'@example.com',
            'is_root' => false,
            'is_verified' => false,
            'is_activated' => true,
            'email_verified_at' => null,
            'verification_token' => 'verify-token',
        ]));

        $this->get(route('defly_manager.verification_mail', [
            'email' => $user->email,
            'token' => 'wrong-token',
        ]))->assertNotFound();

        $this->get(route('defly_manager.verification_mail', [
            'email' => $user->email,
            'token' => 'verify-token',
        ]))->assertRedirect(route('filament.defly-manager.pages.dashboard'));

        $this->assertTrue($user->fresh()->is_verified);
        $this->assertNull($user->fresh()->verification_token);
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_non_root_user_cannot_set_root_flag_when_creating_users(): void
    {
        $regular = User::factory()->create([
            'email' => 'regular-create@example.com',
            'is_root' => false,
            'is_verified' => true,
            'is_activated' => true,
        ]);

        $request = new class($regular) extends UserRequest
        {
            public function __construct(private User $currentUser)
            {
                parent::__construct();
            }

            public function validated($key = null, $default = null): array
            {
                return [
                    'name' => 'Non Root Created',
                    'email' => 'non-root-created@example.com',
                    'password' => 'secret-pass',
                    'is_activated' => true,
                    'is_verified' => true,
                    'is_root' => true,
                ];
            }

            public function user($guard = null): User
            {
                return $this->currentUser;
            }

            public function isMethod($method): bool
            {
                return strtolower((string) $method) === 'post';
            }
        };

        $reflection = new \ReflectionMethod(UserController::class, 'userData');
        $reflection->setAccessible(true);
        $data = $reflection->invoke(new UserController(), $request);

        $this->assertSame([
                'name' => 'Non Root Created',
                'email' => 'non-root-created@example.com',
                'password' => 'secret-pass',
                'is_activated' => true,
                'is_verified' => true,
            ], $data);
    }
}
