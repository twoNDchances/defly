<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\GeneratesFiles;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('custom:observer {name : Model or observer name} {--force : Overwrite existing file}')]
#[Description('Create an observer class in app/Observers with use Before, After;.')]
class CustomObserverCommand extends Command
{
    use GeneratesFiles;

    public function handle(): int
    {
        $modelName = $this->normalizeStudlyName((string) $this->argument('name'));
        $modelName = $this->removeSuffix($modelName, 'Observer');

        if ($modelName === '') {
            $this->error('Observer name is required.');

            return self::FAILURE;
        }

        $observerName = "{$modelName}Observer";
        $path = app_path("Observers/{$observerName}.php");

        $this->writeFile($path, $this->stub($observerName), (bool) $this->option('force'));

        return self::SUCCESS;
    }

    protected function stub(string $observerName): string
    {
        return <<<PHP
<?php

namespace App\Observers;

use App\Traits\Observers\After;
use App\Traits\Observers\Before;

class {$observerName}
{
    use Before, After;

    //
}
PHP;
    }
}
