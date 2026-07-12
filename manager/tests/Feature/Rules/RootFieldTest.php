<?php

namespace Tests\Feature\Rules;

use App\Models\User;
use App\Rules\User\RootField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ValidationRuleTestHelpers;
use Tests\TestCase;

class RootFieldTest extends TestCase
{
    use RefreshDatabase;
    use ValidationRuleTestHelpers;

    public function test_root_field_allows_root_assignment_only_for_root_users(): void
    {
        $this->assertValidatorFails([
            'is_root' => true,
        ], ['is_root' => [new RootField]], 'is_root');

        $this->assertValidatorPasses([
            'is_root' => false,
        ], ['is_root' => [new RootField]]);

        /** @var User $user */
        $user = User::factory()->create([
            'is_root' => true,
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $this->actingAs($user);

        $this->assertValidatorPasses([
            'is_root' => true,
        ], ['is_root' => [new RootField]]);
    }
}
