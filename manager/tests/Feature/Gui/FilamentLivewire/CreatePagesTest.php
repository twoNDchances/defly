<?php

namespace Tests\Feature\Gui\FilamentLivewire;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FilamentLivewireTestHelpers;
use Tests\TestCase;

class CreatePagesTest extends TestCase
{
    use FilamentLivewireTestHelpers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpFilamentLivewire();
    }

    public function test_resource_create_pages_render_filament_forms(): void
    {
        $this->seedRecords();

        foreach ($this->createPages() as $page) {
            $this->livewirePage($page)
                ->assertOk()
                ->assertSchemaExists('form');
        }
    }
}
