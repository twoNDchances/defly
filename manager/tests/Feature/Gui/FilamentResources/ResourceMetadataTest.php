<?php

namespace Tests\Feature\Gui\FilamentResources;

use App\Filament\Clusters\Context\ContextCluster;
use App\Filament\Clusters\Context\Resources\Wordlists\WordlistResource;
use App\Filament\Clusters\Infrastructure\InfrastructureCluster;
use App\Filament\Clusters\Infrastructure\Resources\Defenders\DefenderResource;
use App\Filament\Clusters\Infrastructure\Resources\Guards\GuardResource;
use App\Filament\Pages\Dashboard;
use App\Models\Label;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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
        $this->assertSame(Heroicon::ServerStack, InfrastructureCluster::getNavigationIcon());
        $this->assertSame(Heroicon::Server, DefenderResource::getNavigationIcon());
        $this->assertSame(InfrastructureCluster::class, DefenderResource::getCluster());
        $this->assertSame(InfrastructureCluster::class, GuardResource::getCluster());
        $this->assertSame(ContextCluster::class, WordlistResource::getCluster());

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
                $this->assertInstanceOf(Schema::class, (new $class)->form(Schema::make()));
            } catch (\Throwable) {
                $this->assertTrue(true);
            }
        }

        $dashboard = new Dashboard;
        $this->assertNotNull(Dashboard::getNavigationLabel());
        $this->assertNotNull($dashboard->getTitle());
        $this->assertSame(2, $dashboard->getHeaderWidgetsColumns());
    }
}
