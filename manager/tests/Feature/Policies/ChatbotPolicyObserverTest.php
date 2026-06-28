<?php

namespace Tests\Feature\Policies;

use App\Filament\Pages\Assistant;
use App\Models\Conservation;
use App\Models\Message;
use App\Models\Permission;
use App\Models\Timeline;
use App\Models\User;
use App\Policies\ConservationPolicy;
use App\Services\Security;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use ReflectionProperty;
use Tests\TestCase;

class ChatbotPolicyObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_chatbot_uses_only_the_conservation_policy(): void
    {
        $owner = User::factory()->create([
            'is_root' => true,
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $other = User::factory()->create([
            'is_root' => true,
            'is_verified' => true,
            'is_activated' => true,
        ]);

        $this->actingAs($owner);
        $conservation = Conservation::query()->create(['title' => 'Private chat']);
        $conservation->messages()->create([
            'role' => 'user',
            'content' => 'Private message',
        ]);

        $this->assertInstanceOf(
            ConservationPolicy::class,
            Gate::getPolicyFor(Conservation::class),
        );
        $this->assertNull(Gate::getPolicyFor(Message::class));
        $this->assertArrayNotHasKey('Message', Security::generatePermissionList(true));
        $this->assertArrayHasKey('chat', Security::generatePermissionList(true)['Conservation']);
        $this->assertArrayHasKey('pin', Security::generatePermissionList(true)['Conservation']);
        $this->assertTrue(Gate::allows('view', $conservation));
        $this->assertTrue(Gate::allows('update', $conservation));
        $this->assertTrue(Gate::allows('delete', $conservation));
        $this->assertTrue(Gate::allows('chat', $conservation));
        $this->assertTrue(Gate::allows('pin', $conservation));

        $this->actingAs($other);
        $this->assertTrue(Gate::allows('view', $conservation));
        $this->assertTrue(Gate::allows('update', $conservation));
        $this->assertTrue(Gate::allows('delete', $conservation));
    }

    public function test_view_any_controls_page_access_and_chat_uses_its_own_permission(): void
    {
        $user = User::factory()->create([
            'is_root' => false,
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $viewAny = Permission::withoutEvents(fn () => Permission::query()->create([
            'name' => 'Conservation:List',
            'applied_for' => 'Conservation',
            'action' => 'viewAny',
            'created_by' => $user->id,
        ]));
        $chat = Permission::withoutEvents(fn () => Permission::query()->create([
            'name' => 'Conservation:Chat',
            'applied_for' => 'Conservation',
            'action' => 'chat',
            'created_by' => $user->id,
        ]));
        $user->permissions()->attach($viewAny);

        $this->actingAs($user);

        $this->assertTrue(Assistant::canAccess());
        $this->assertFalse(Gate::allows('chat', Conservation::class));

        $user->permissions()->attach($chat);
        $this->assertTrue(Gate::allows('chat', Conservation::class));
    }

    public function test_conservation_observer_assigns_ownership_and_message_touches_its_conservation(): void
    {
        $owner = User::factory()->create([
            'is_root' => true,
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $this->actingAs($owner);

        $conservation = Conservation::query()->create(['title' => 'Observed chat']);
        $this->assertSame($owner->id, $conservation->created_by);

        $oldTimestamp = now()->subDay();
        $conservation->forceFill(['updated_at' => $oldTimestamp])->saveQuietly();

        $message = $conservation->messages()->create([
            'role' => 'user',
            'content' => 'Touch after create',
        ]);
        $this->assertTrue($conservation->fresh()->updated_at->isAfter($oldTimestamp));
        $this->assertTrue($message->exists);
    }

    public function test_message_events_do_not_write_timelines(): void
    {
        $consoleFlag = new ReflectionProperty($this->app, 'isRunningInConsole');
        $consoleFlag->setAccessible(true);
        $previous = $consoleFlag->getValue($this->app);
        $consoleFlag->setValue($this->app, false);

        try {
            $user = User::factory()->create([
                'is_root' => true,
                'is_verified' => true,
                'is_activated' => true,
            ]);
            $this->actingAs($user);
            $conservation = Conservation::query()->create(['title' => 'No message audit']);
            $message = $conservation->messages()->create([
                'role' => 'user',
                'content' => 'Do not write this to Timeline.',
            ]);

            $this->assertFalse(Timeline::query()
                ->where('resource_type', Message::class)
                ->where('resource_id', $message->id)
                ->exists());
        } finally {
            $consoleFlag->setValue($this->app, $previous);
        }
    }
}
