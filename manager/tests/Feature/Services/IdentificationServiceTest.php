<?php

namespace Tests\Feature\Services;

use App\Models\User;
use App\Services\Identification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdentificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_identification_service_reads_current_authenticated_user_state(): void
    {
        /** @var User $root */
        $root = User::factory()->create(['is_root' => true, 'is_verified' => true, 'is_activated' => true]);
        $this->actingAs($root);

        $this->assertSame($root->name, Identification::getName());
        $this->assertTrue(Identification::isActivated());
        $this->assertTrue(Identification::isVerified());
        $this->assertNotNull(Identification::getUsers());
        $this->assertNull(Identification::getCreatedBy());
    }
}
