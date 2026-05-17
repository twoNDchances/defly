<?php

namespace Tests\Feature\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_scope_and_created_record_relationships_are_available(): void
    {
        $root = User::factory()->create(['is_root' => true, 'is_verified' => true, 'is_activated' => true]);
        $regular = User::factory()->create(['is_root' => false, 'is_verified' => true, 'is_activated' => true]);
        $this->actingAs($root);

        $this->assertGreaterThanOrEqual(1, User::query()->excludeRoot()->count());
        $this->actingAs($regular);
        $this->assertSame(0, User::query()->excludeRoot()->where('is_root', true)->count());

        foreach ([
            'getUsers',
            'getGroups',
            'getPermissions',
            'getLabels',
            'getWordlists',
            'getEngines',
            'getTargets',
            'getActions',
            'getRules',
            'getPrinciples',
            'getDecisions',
            'getDefenders',
            'getKeys',
            'getTimelines',
        ] as $method) {
            $this->assertInstanceOf(HasMany::class, $root->{$method}());
        }
    }
}
