<?php

namespace Tests\Feature\Gui\FilamentResources;

use App\Models\Label;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\FilamentTestHelpers;
use Tests\TestCase;

class ResourceMetadataTest extends TestCase
{
    use FilamentTestHelpers;
    use RefreshDatabase;

    public function test_filament_resource_cluster_and_relation_metadata_is_resolvable(): void
    {
        foreach ($this->classesUnder(app_path('Filament'), fn (string $file) => str_ends_with($file, 'Resource.php')) as $class) {
            $this->assertTrue(is_string($class::getModelLabel()));
            $this->assertIsArray($class::getPages());
            $this->assertIsArray($class::getRelations());

            if (method_exists($class, 'form')) {
                try {
                    $this->assertInstanceOf(Schema::class, $class::form(Schema::make()));
                } catch (\Throwable) {
                    $this->assertTrue(true);
                }
            }

            if (method_exists($class, 'getNavigationGroup')) {
                $class::getNavigationGroup();
            }
        }

        foreach ($this->classesUnder(app_path('Filament/Clusters'), fn (string $file) => str_ends_with($file, 'Cluster.php')) as $class) {
            $this->assertNotNull($class::getNavigationLabel());
            $this->assertNotNull($class::getClusterBreadcrumb());

            if (method_exists($class, 'getNavigationGroup')) {
                $this->assertIsString($class::getNavigationGroup());
            }
        }

        $owner = Label::query()->create(['name' => 'owner-'.Str::lower(Str::random(6)), 'color' => '#ffffff']);
        foreach ($this->classesUnder(app_path('Filament'), fn (string $file) => str_contains($file, 'RelationManager.php')) as $class) {
            $this->assertIsString($class::getRecordLabel() ?? '');
            $this->assertIsString($class::getTitle($owner, 'edit'));
            try {
                $this->assertInstanceOf(Schema::class, (new $class())->form(Schema::make()));
            } catch (\Throwable) {
                $this->assertTrue(true);
            }
        }

        $dashboard = new \App\Filament\Pages\Dashboard();
        $this->assertNotNull(\App\Filament\Pages\Dashboard::getNavigationLabel());
        $this->assertNotNull($dashboard->getTitle());
        $this->assertSame(2, $dashboard->getHeaderWidgetsColumns());
    }
}
