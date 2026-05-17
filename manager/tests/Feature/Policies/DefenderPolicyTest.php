<?php

namespace Tests\Feature\Policies;

use App\Enums\Defender\DeploymentStatus;
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
        $root = User::factory()->create(['is_root' => true, 'is_verified' => true, 'is_activated' => true]);
        $regular = User::factory()->create(['is_root' => false, 'is_verified' => true, 'is_activated' => true]);
        $this->actingAs($root);

        $defenderPolicy = new DefenderPolicy();
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
}
