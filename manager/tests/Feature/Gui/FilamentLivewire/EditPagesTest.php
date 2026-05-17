<?php

namespace Tests\Feature\Gui\FilamentLivewire;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FilamentLivewireTestHelpers;
use Tests\TestCase;

class EditPagesTest extends TestCase
{
    use FilamentLivewireTestHelpers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpFilamentLivewire();
    }

    public function test_resource_edit_pages_render_filament_forms(): void
    {
        $records = $this->seedRecords();

        foreach ($this->editPages($records) as [$page, $record]) {
            try {
                $this->livewirePage($page, ['record' => $record->getKey()])
                    ->assertOk()
                    ->assertSchemaExists('form');
            } catch (\PHPUnit\Framework\AssertionFailedError $exception) {
                $this->fail("{$page} failed assertions: {$exception->getMessage()}");
            }
        }
    }
}
