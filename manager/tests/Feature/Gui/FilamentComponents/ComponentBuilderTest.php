<?php

namespace Tests\Feature\Gui\FilamentComponents;

use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\Support\FilamentTestHelpers;
use Tests\TestCase;

class ComponentBuilderTest extends TestCase
{
    use FilamentTestHelpers;
    use RefreshDatabase;

    public function test_component_builders_and_schema_table_configurators_are_resolvable(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_root' => true,
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $this->actingAs($user);

        foreach ($this->classesUnder(app_path('Filament/Components'), fn (string $file) => str_ends_with($file, 'Form.php') || str_ends_with($file, 'Table.php')) as $class) {
            $this->assertTrue(method_exists($class, 'build'), "{$class} must expose build().");
            $components = $class::build();
            $this->assertIsArray($components, "{$class}::build should return an array.");
            $this->assertNotEmpty($components, "{$class}::build should not be empty.");
        }

        foreach ($this->classesUnder(app_path('Filament'), fn (string $file) => str_ends_with($file, 'Form.php')) as $class) {
            if (! method_exists($class, 'configure')) {
                continue;
            }

            $this->assertInstanceOf(Schema::class, $class::configure(Schema::make()), "{$class}::configure should return a schema.");
        }

        foreach ($this->classesUnder(app_path('Filament'), fn (string $file) => str_ends_with($file, 'Table.php')) as $class) {
            if (! method_exists($class, 'configure')) {
                continue;
            }

            $livewire = Mockery::mock(HasTable::class);
            $this->assertInstanceOf(HasTable::class, $livewire);
            $table = Table::make($livewire);

            $this->assertInstanceOf(Table::class, $class::configure($table), "{$class}::configure should return a table.");
        }
    }
}
