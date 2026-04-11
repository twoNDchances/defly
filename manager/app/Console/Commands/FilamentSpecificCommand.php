<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\GeneratesFiles;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('custom:trait:specific {name : Specific name} {--force : Overwrite existing files}')]
#[Description('Create Filament specific traits (Button, Column, Data, Field).')]
class FilamentSpecificCommand extends Command
{
    use GeneratesFiles;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->normalizeStudlyName((string) $this->argument('name'));
        if ($name === '') {
            $this->error('Specific name is required.');

            return self::FAILURE;
        }

        $force = (bool) $this->option('force');
        $directory = app_path("Traits/Filament/Specifics/{$name}");

        $this->ensureDirectory($directory);

        $files = [
            "{$directory}/{$name}Button.php" => $this->buttonStub($name),
            "{$directory}/{$name}Column.php" => $this->columnStub($name),
            "{$directory}/{$name}Data.php" => $this->dataStub($name),
            "{$directory}/{$name}Field.php" => $this->fieldStub($name),
        ];

        foreach ($files as $path => $contents) {
            $this->writeFile($path, $contents, $force);
        }

        return self::SUCCESS;
    }

    protected function buttonStub(string $name): string
    {
        return <<<PHP
<?php

namespace App\Traits\Filament\Specifics\\{$name};

use App\Traits\Filament\Generals\Components\Button;

trait {$name}Button
{
    use Button;

    //
}
PHP;
    }

    protected function columnStub(string $name): string
    {
        return <<<PHP
<?php

namespace App\Traits\Filament\Specifics\\{$name};

use App\Traits\Filament\Generals\Components\Column;

trait {$name}Column
{
    use Column, {$name}Button, {$name}Data;

    //
}
PHP;
    }

    protected function dataStub(string $name): string
    {
        return <<<PHP
<?php

namespace App\Traits\Filament\Specifics\\{$name};

trait {$name}Data
{
    //
}
PHP;
    }

    protected function fieldStub(string $name): string
    {
        return <<<PHP
<?php

namespace App\Traits\Filament\Specifics\\{$name};

use App\Traits\Filament\Generals\Components\Field;

trait {$name}Field
{
    use Field, {$name}Button, {$name}Data;

    //
}
PHP;
    }
}
