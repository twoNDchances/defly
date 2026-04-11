<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\GeneratesFiles;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('custom:trait:model {name : Trait name} {--force : Overwrite existing file}')]
#[Description('Create a trait file in app/Traits/Models.')]
class ModelTraitCommand extends Command
{
    use GeneratesFiles;

    public function handle(): int
    {
        $name = $this->normalizeStudlyName((string) $this->argument('name'));
        $name = $this->removeSuffix($name, 'Trait');

        if ($name === '') {
            $this->error('Trait name is required.');

            return self::FAILURE;
        }

        $path = app_path("Traits/Models/{$name}.php");
        $this->writeFile($path, $this->stub($name), (bool) $this->option('force'));

        return self::SUCCESS;
    }

    protected function stub(string $name): string
    {
        return <<<PHP
<?php

namespace App\Traits\Models;

trait {$name}
{
    //
}
PHP;
    }
}
