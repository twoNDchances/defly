<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\GeneratesFiles;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('custom:service {name : Service class name} {--force : Overwrite existing file}')]
#[Description('Create a service class in app/Services.')]
class ServiceCommand extends Command
{
    use GeneratesFiles;

    public function handle(): int
    {
        $name = $this->normalizeStudlyName((string) $this->argument('name'));

        if ($name === '') {
            $this->error('Service name is required.');

            return self::FAILURE;
        }

        $path = app_path("Services/{$name}.php");
        $this->writeFile($path, $this->stub($name), (bool) $this->option('force'));

        return self::SUCCESS;
    }

    protected function stub(string $name): string
    {
        return <<<PHP
<?php

namespace App\Services;

class {$name}
{
    //
}
PHP;
    }
}
