<?php

namespace Tests\Feature\Services;

use App\Models\Action;
use App\Models\Decision;
use App\Models\Defender;
use App\Models\Group;
use App\Models\Guard;
use App\Models\Key;
use App\Models\Rule;
use App\Models\User;
use App\Services\Security;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\DomainTestHelpers;
use Tests\TestCase;

class SecurityServiceTest extends TestCase
{
    use DomainTestHelpers;
    use RefreshDatabase;

    public function test_permission_checks_support_root_direct_group_and_authenticated_key_subjects(): void
    {
        $root = User::factory()->create(['is_root' => true, 'is_verified' => true, 'is_activated' => true]);
        $user = User::factory()->create(['is_root' => false, 'is_verified' => true, 'is_activated' => true]);
        $inactive = User::factory()->create(['is_verified' => true, 'is_activated' => false]);
        $directPermission = $this->permission('Action:List', 'Action', 'viewAny');
        $groupPermission = $this->permission('Rule:Update', 'Rule', 'update');

        $this->assertTrue(Security::can(Action::class, 'delete', $root));
        $this->assertFalse(Security::can(Action::class, 'viewAny', $inactive));
        $this->assertFalse(Security::can(Action::class, 'viewAny', $user));

        $user->permissions()->attach($directPermission->id);
        $this->assertTrue(Security::can(Action::class, 'viewAny', $user));

        $group = Group::query()->create(['name' => 'security-'.Str::lower(Str::random(6))]);
        $group->permissions()->attach($groupPermission->id);
        $user->groups()->attach($group->id);
        $this->assertTrue(Security::can(Rule::class, 'update', $user));

        $key = Key::withoutEvents(fn () => Key::query()->create([
            'name' => 'ephemeral-key',
            'token' => 'plain-token',
            'is_reused' => false,
            'created_by' => $user->id,
        ]));
        $key->permissions()->attach($this->permission('Decision:List', 'Decision', 'viewAny')->id);
        $this->app['request']->attributes->set('authenticated_key', $key);
        $this->assertTrue(Security::can(Decision::class, 'viewAny', $user));

        $key->forceFill(['is_reused' => true])->save();
        $this->app['request']->attributes->set('authenticated_key', $key->refresh());
        $this->assertFalse(Security::can(Decision::class, 'viewAny', $user));
    }

    public function test_permission_lists_and_all_action_permissions_are_supported(): void
    {
        $flatPermissions = Security::generatePermissionList(false);
        $this->assertNotEmpty($flatPermissions);
        $this->assertArrayHasKey('Action', Security::generatePermissionList(true));

        $authorizedUser = User::factory()->create([
            'is_root' => false,
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $authorizedUser->permissions()->attach($this->permission('Action:Full', 'Action', 'all')->id);
        $this->assertTrue(Security::can(Action::class, 'delete', $authorizedUser));

        $group = Group::query()->create(['name' => 'empty-group-'.Str::lower(Str::random(6))]);
        $authorizedUser->groups()->attach($group->id);
        $this->assertFalse(Security::checkPermission($authorizedUser, Rule::class, 'delete'));
    }

    public function test_defender_guard_access_requires_active_matching_guard_only_when_guarded(): void
    {
        $user = User::factory()->create([
            'email' => 'guarded-operator@example.com',
            'is_root' => false,
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $defender = $this->defender('guard-access', null);

        $this->assertTrue(Security::canOperateDefender($defender, null));
        $this->assertTrue(Security::requesterCanOperateDefender($defender, null));

        $activeGuard = Guard::query()->create([
            'name' => 'active-guard',
            'expired_at' => now()->addHour(),
        ]);
        $activeGuard->defenders()->attach($defender->id);

        $this->assertFalse(Security::canOperateDefender($defender, $user));
        $this->assertFalse(Security::requesterCanOperateDefender($defender, $user->email));

        $activeGuard->users()->attach($user->id);

        $this->assertTrue(Security::canOperateDefender($defender, $user));
        $this->assertTrue(Security::requesterCanOperateDefender($defender, 'Guarded-Operator@Example.com'));

        $activeGuard->forceFill(['expired_at' => now()->subMinute()])->save();

        $this->assertFalse(Security::canOperateDefender($defender, $user));
        $this->assertFalse(Security::requesterCanOperateDefender($defender, $user->email));

        $owner = User::factory()->create([
            'email' => 'guard-owner@example.com',
            'is_root' => false,
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $ownedDefender = $this->defender('guard-owner-access', null);
        $ownedDefender->forceFill(['created_by' => $owner->id])->saveQuietly();
        $activeGuard->defenders()->attach($ownedDefender->id);

        $this->assertTrue(Security::canOperateDefender($ownedDefender, $owner));
        $this->assertTrue(Security::requesterCanOperateDefender($ownedDefender, 'Guard-Owner@Example.com'));
    }

    public function test_defender_visibility_scope_limits_guarded_defenders_to_owner_or_active_guard_users(): void
    {
        $viewer = User::factory()->create([
            'is_root' => false,
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $publicDefender = $this->defender('public-defender', null);
        $guardedDefender = $this->defender('guarded-defender', null);
        $ownedDefender = $this->defender('owned-defender', null);
        $expiredGuardDefender = $this->defender('expired-guard-defender', null);
        $ownedDefender->forceFill(['created_by' => $viewer->id])->saveQuietly();

        $activeGuard = Guard::query()->create([
            'name' => 'visibility-active-guard',
            'expired_at' => now()->addHour(),
        ]);
        $activeGuard->defenders()->attach([$guardedDefender->id, $ownedDefender->id]);

        $expiredGuard = Guard::query()->create([
            'name' => 'visibility-expired-guard',
            'expired_at' => now()->subMinute(),
        ]);
        $expiredGuard->defenders()->attach($expiredGuardDefender->id);

        $visibleIds = Defender::query()->visibleTo($viewer)->pluck('id')->all();

        $this->assertContains($publicDefender->id, $visibleIds);
        $this->assertContains($ownedDefender->id, $visibleIds);
        $this->assertNotContains($guardedDefender->id, $visibleIds);
        $this->assertNotContains($expiredGuardDefender->id, $visibleIds);

        $activeGuard->users()->attach($viewer->id);
        $visibleIds = Defender::query()->visibleTo($viewer)->pluck('id')->all();

        $this->assertContains($guardedDefender->id, $visibleIds);
        $this->assertNotContains($expiredGuardDefender->id, $visibleIds);
    }
}
