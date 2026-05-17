<?php

namespace Tests\Feature\Gui\FilamentLivewire;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FilamentLivewireTestHelpers;
use Tests\TestCase;

class RelationManagersTest extends TestCase
{
    use FilamentLivewireTestHelpers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpFilamentLivewire();
    }

    public function test_relation_managers_render_filament_tables(): void
    {
        $records = $this->seedRecords();

        foreach ($this->relationManagers($records) as [$manager, $owner, $page]) {
            $this->livewirePage($manager, [
                'ownerRecord' => $owner,
                'pageClass' => $page,
            ])->assertOk();
        }
    }
}
