<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Livewire\Livewire;

abstract class TestCase extends BaseTestCase {
    protected $primaryUser = User::factory()->create();

    protected $secondaryUser = User::factory()->create();

    protected static $livewire = Livewire::class;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function login($user): void
    {
        $this->actingAs($user);
    }

    protected function logout(): void
    {
        $this->actingAsGuest();
    }
}
