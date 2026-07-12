<?php

namespace Tests\Feature\Models;

use App\Models\Conservation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConservationMessageModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_conservation_and_message_relationships_are_available(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $conservation = Conservation::query()->create([
            'title' => 'First conservation',
            'is_pinned' => true,
            'created_by' => $user->id,
        ]);
        $message = $conservation->messages()->create([
            'role' => 'assistant',
            'content' => 'Hello from AI.',
            'resources' => [[
                'type' => 'label',
                'id' => 'resource-id',
                'label' => 'Resource label',
            ]],
        ]);

        $this->assertTrue($conservation->is_pinned);
        $this->assertTrue($conservation->createdBy->is($user));
        $this->assertTrue($user->getConservations()->whereKey($conservation->id)->exists());
        $this->assertTrue($conservation->messages->first()->is($message));
        $this->assertTrue($message->conservation->is($conservation));
        $this->assertSame('resource-id', $message->resources[0]['id']);
        $this->assertInstanceOf(Message::class, $message);
    }
}
