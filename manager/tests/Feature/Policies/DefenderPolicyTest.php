<?php

namespace Tests\Feature\Policies;

use App\Enums\Defender\DeploymentStatus;
use App\Models\Guard;
use App\Models\Permission;
use App\Models\User;
use App\Policies\DefenderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\ModelTestHelpers;
use Tests\TestCase;

class DefenderPolicyTest extends TestCase
{
    use ModelTestHelpers;
    use RefreshDatabase;

    public function test_defender_policy_blocks_protected_deployment_states_and_honors_permissions(): void
    {
        /** @var User $root */
        $root = User::factory()->create(['is_root' => true, 'is_verified' => true, 'is_activated' => true]);
        /** @var User $regular */
        $regular = User::factory()->create(['is_root' => false, 'is_verified' => true, 'is_activated' => true]);
        $this->actingAs($root);

        $defenderPolicy = new DefenderPolicy;
        $pendingDefender = $this->modelDefender(DeploymentStatus::Pending->value);
        $successfulDefender = $this->modelDefender(DeploymentStatus::Successful->value);
        $failedDefender = $this->modelDefender(DeploymentStatus::Failed->value);
        $this->assertFalse($defenderPolicy->update($root, $pendingDefender));
        $this->assertFalse($defenderPolicy->delete($root, $pendingDefender));
        $this->assertFalse($defenderPolicy->delete($root, $successfulDefender));
        $this->assertTrue($defenderPolicy->delete($root, $failedDefender));
        $this->assertFalse($defenderPolicy->deploy($root, $pendingDefender));
        $this->assertTrue($defenderPolicy->cancel($root, $successfulDefender));
        $this->assertFalse($defenderPolicy->cancel($root, $failedDefender));
        $this->assertFalse($defenderPolicy->follow($root, $failedDefender));
        $this->assertFalse($defenderPolicy->refresh($root, $failedDefender));

        $regular->permissions()->attach(Permission::query()->create([
            'name' => 'defender-delete-'.Str::lower(Str::random(6)),
            'applied_for' => 'Defender',
            'action' => 'delete',
        ])->id);
        $this->actingAs($regular);
        $this->assertTrue($defenderPolicy->delete($regular, $failedDefender));
    }

    public function test_defender_policy_requires_active_guard_for_guarded_defenders(): void
    {
        /** @var User $root */
        $root = User::factory()->create(['is_root' => true, 'is_verified' => true, 'is_activated' => true]);
        $this->actingAs($root);

        $defenderPolicy = new DefenderPolicy;
        $defender = $this->modelDefender(DeploymentStatus::Failed->value);
        $owner = User::factory()->create(['is_root' => false, 'is_verified' => true, 'is_activated' => true]);
        $defender->forceFill(['created_by' => $owner->id])->saveQuietly();
        $guard = Guard::query()->create([
            'name' => 'policy-guard',
            'expired_at' => now()->addHour(),
        ]);
        $guard->defenders()->attach($defender->id);

        $this->assertFalse($defenderPolicy->view($root, $defender));
        $this->assertFalse($defenderPolicy->delete($root, $defender));

        $guard->users()->attach($root->id);
        $this->assertTrue($defenderPolicy->view($root, $defender));
        $this->assertTrue($defenderPolicy->delete($root, $defender));

        $guard->forceFill(['expired_at' => now()->subMinute()])->save();
        $this->assertFalse($defenderPolicy->delete($root, $defender));

        $owner->permissions()->attach(Permission::query()->create([
            'name' => 'defender-owner-delete-'.Str::lower(Str::random(6)),
            'applied_for' => 'Defender',
            'action' => 'delete',
        ])->id);
        $this->actingAs($owner);
        $this->assertTrue($defenderPolicy->delete($owner, $defender));
    }
}
