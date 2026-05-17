<?php

namespace Tests\Feature\Policies;

use App\Models\User;
use App\Policies\UserPolicy;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_panel_access_and_policy_protect_root_and_self_records(): void
    {
        $root = User::factory()->create(['is_root' => true, 'is_verified' => true, 'is_activated' => true]);
        $regular = User::factory()->create(['is_root' => false, 'is_verified' => true, 'is_activated' => true]);
        $inactive = User::factory()->create(['is_verified' => true, 'is_activated' => false]);
        $this->actingAs($root);

        $this->assertTrue($root->canAccessPanel(Panel::make()->id('defly-manager')));
        $this->assertFalse($inactive->canAccessPanel(Panel::make()->id('defly-manager')));

        $userPolicy = new UserPolicy();
        $this->assertSame(User::class, $userPolicy->getModel());
        $this->assertFalse($userPolicy->view($regular, $root));
        $this->assertFalse($userPolicy->update($regular, $regular));
    }
}
