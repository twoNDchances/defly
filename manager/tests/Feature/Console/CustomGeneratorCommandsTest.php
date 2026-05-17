<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CustomGeneratorCommandsTest extends TestCase
{
    private array $cleanupPaths = [];

    protected function tearDown(): void
    {
        foreach ($this->cleanupPaths as $path) {
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);
            } elseif (File::exists($path)) {
                File::delete($path);
            }
        }

        parent::tearDown();
    }

    public function test_custom_artisan_generators_create_skip_and_overwrite_files(): void
    {
        $files = [
            app_path('Models/CodexGenerated.php'),
            app_path('Observers/CodexGeneratedObserver.php'),
            app_path('Policies/CodexGeneratedPolicy.php'),
            app_path('Services/CodexGeneratedService.php'),
            app_path('Traits/Models/CodexGenerated.php'),
            app_path('Traits/Requests/CodexGenerated.php'),
            app_path('Traits/Observers/CodexGenerated.php'),
            app_path('Traits/Policies/CodexGenerated.php'),
        ];
        $directories = [
            app_path('Filament/Components/CodexGenerated'),
            app_path('Traits/Filament/Specifics/CodexGenerated'),
        ];
        $this->cleanupPaths = [...$directories, ...$files];

        $this->artisan('custom:model CodexGeneratedModel --force')->assertSuccessful();
        $this->artisan('custom:model CodexGenerated')->assertSuccessful();
        $this->assertFileContains(app_path('Models/CodexGenerated.php'), 'class CodexGenerated extends Model');

        $this->artisan('custom:observer CodexGeneratedObserver --force')->assertSuccessful();
        $this->assertFileContains(app_path('Observers/CodexGeneratedObserver.php'), 'class CodexGeneratedObserver');

        $this->artisan('custom:policy CodexGeneratedPolicy --force')->assertSuccessful();
        $this->assertFileContains(app_path('Policies/CodexGeneratedPolicy.php'), 'return CodexGenerated::class;');

        $this->artisan('custom:service CodexGeneratedService --force')->assertSuccessful();
        $this->assertFileContains(app_path('Services/CodexGeneratedService.php'), 'class CodexGeneratedService');

        $this->artisan('custom:trait:model CodexGeneratedTrait --force')->assertSuccessful();
        $this->assertFileContains(app_path('Traits/Models/CodexGenerated.php'), 'trait CodexGenerated');

        $this->artisan('custom:trait:request CodexGeneratedTrait --force')->assertSuccessful();
        $this->assertFileContains(app_path('Traits/Requests/CodexGenerated.php'), 'trait CodexGenerated');

        $this->artisan('custom:trait:observer CodexGeneratedTrait --force')->assertSuccessful();
        $this->assertFileContains(app_path('Traits/Observers/CodexGenerated.php'), 'trait CodexGenerated');

        $this->artisan('custom:trait:policy CodexGeneratedTrait --force')->assertSuccessful();
        $this->assertFileContains(app_path('Traits/Policies/CodexGenerated.php'), 'trait CodexGenerated');

        $this->artisan('custom:component CodexGeneratedComponent --force')->assertSuccessful();
        $this->assertFileContains(app_path('Filament/Components/CodexGenerated/CodexGeneratedTable.php'), 'class CodexGeneratedTable');
        $this->assertFileContains(app_path('Filament/Components/CodexGenerated/CodexGeneratedForm.php'), 'class CodexGeneratedForm');

        $this->artisan('custom:trait:specific CodexGenerated --force')->assertSuccessful();
        $this->assertFileContains(app_path('Traits/Filament/Specifics/CodexGenerated/CodexGeneratedButton.php'), 'trait CodexGeneratedButton');
        $this->assertFileContains(app_path('Traits/Filament/Specifics/CodexGenerated/CodexGeneratedColumn.php'), 'trait CodexGeneratedColumn');
        $this->assertFileContains(app_path('Traits/Filament/Specifics/CodexGenerated/CodexGeneratedData.php'), 'trait CodexGeneratedData');
        $this->assertFileContains(app_path('Traits/Filament/Specifics/CodexGenerated/CodexGeneratedField.php'), 'trait CodexGeneratedField');
    }

    private function assertFileContains(string $path, string $needle): void
    {
        $this->assertFileExists($path);
        $this->assertStringContainsString($needle, File::get($path));
    }
}
