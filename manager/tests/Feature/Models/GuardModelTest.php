<?php

namespace Tests\Feature\Models;

use App\Models\Guard;
use App\Models\User;
use App\Policies\GuardPolicy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Tests\Support\ModelTestHelpers;
use Tests\TestCase;

class GuardModelTest extends TestCase
{
    use ModelTestHelpers;
    use RefreshDatabase;

    public function test_guard_uses_its_observer_casts_and_relationships(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create([
            'is_root' => true,
            'is_verified' => true,
            'is_activated' => true,
        ]);
        /** @var User $user */
        $user = User::factory()->create();
        $defender = $this->modelDefender();
        $this->actingAs($owner);

        $guard = Guard::query()->create([
            'name' => 'guard-'.Str::lower(Str::random(6)),
            'description' => 'Test guard',
            'expired_at' => now()->addDay(),
        ]);

        $guard->users()->attach($user);
        $guard->defenders()->attach($defender);

        $this->assertSame($owner->id, $guard->created_by);
        $this->assertInstanceOf(\DateTimeInterface::class, $guard->expired_at);
        $this->assertInstanceOf(BelongsToMany::class, $guard->users());
        $this->assertInstanceOf(BelongsToMany::class, $guard->defenders());
        $this->assertTrue($guard->users()->whereKey($user->id)->exists());
        $this->assertTrue($guard->defenders()->whereKey($defender->id)->exists());
    }

    public function test_guard_policy_is_discovered(): void
    {
        /** @var User $root */
        $root = User::factory()->create([
            'is_root' => true,
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $this->actingAs($root);

        $guard = Guard::query()->create([
            'name' => 'guard-'.Str::lower(Str::random(6)),
        ]);

        $this->assertInstanceOf(GuardPolicy::class, Gate::getPolicyFor(Guard::class));
        $this->assertTrue(Gate::allows('view', $guard));
        $this->assertTrue(Gate::allows('update', $guard));
        $this->assertTrue(Gate::allows('delete', $guard));
    }
}
