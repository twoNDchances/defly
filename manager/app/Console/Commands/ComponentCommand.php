<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\GeneratesFiles;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('custom:component {name : Component folder name} {--force : Overwrite existing files}')]
#[Description('Create a Filament component folder with Table and Form classes.')]
class ComponentCommand extends Command
{
    use GeneratesFiles;

    public function handle(): int
    {
        $name = $this->normalizeStudlyName((string) $this->argument('name'));
        $name = $this->removeSuffix($name, 'Component');
        $name = $this->removeSuffix($name, 'Table');
        $name = $this->removeSuffix($name, 'Form');

        if ($name === '') {
            $this->error('Component name is required.');

            return self::FAILURE;
        }

        $directory = app_path("Filament/Components/{$name}");
        $this->ensureDirectory($directory);

        $force = (bool) $this->option('force');
        $this->writeFile("{$directory}/{$name}Table.php", $this->tableStub($name), $force);
        $this->writeFile("{$directory}/{$name}Form.php", $this->formStub($name), $force);

        return self::SUCCESS;
    }

    protected function tableStub(string $name): string
    {
        return <<<PHP
<?php

namespace App\Filament\Components\\{$name};

use App\Traits\Filament\Specifics\\{$name}\\{$name}Column;

class {$name}Table
{
    use {$name}Column;

    public static function build()
    {
        return [];
    }
}
PHP;
    }

    protected function formStub(string $name): string
    {
        return <<<PHP
<?php

namespace App\Filament\Components\\{$name};

use App\Traits\Filament\Specifics\\{$name}\\{$name}Field;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class {$name}Form
{
    use {$name}Field;

    public static function build()
    {
        return [];
    }
}
PHP;
    }
}
