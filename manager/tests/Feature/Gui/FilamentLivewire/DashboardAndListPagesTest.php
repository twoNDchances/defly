<?php

namespace Tests\Feature\Gui\FilamentLivewire;

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\Timelines\Pages\ListTimelines;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FilamentLivewireTestHelpers;
use Tests\TestCase;

class DashboardAndListPagesTest extends TestCase
{
    use FilamentLivewireTestHelpers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpFilamentLivewire();
    }

    public function test_dashboard_and_resource_list_pages_render_filament_tables(): void
    {
        $records = $this->seedRecords();

        $this->livewirePage(Dashboard::class)->assertOk();

        foreach ($this->listPages($records) as [$page, $record]) {
            $component = $this->livewirePage($page)->assertOk();

            if ($page !== ListTimelines::class) {
                $component->assertCanSeeTableRecords(collect([$record]));
            }
        }
    }
}
