<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\GeneratesFiles;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('custom:policy {name : Model or policy name} {--force : Overwrite existing file}')]
#[Description('Create a policy class in app/Policies with use Basic;.')]
class CustomPolicyCommand extends Command
{
    use GeneratesFiles;

    public function handle(): int
    {
        $modelName = $this->normalizeStudlyName((string) $this->argument('name'));
        $modelName = $this->removeSuffix($modelName, 'Policy');

        if ($modelName === '') {
            $this->error('Policy name is required.');

            return self::FAILURE;
        }

        $policyName = "{$modelName}Policy";
        $path = app_path("Policies/{$policyName}.php");

        $this->writeFile($path, $this->stub($policyName, $modelName), (bool) $this->option('force'));

        return self::SUCCESS;
    }

    protected function stub(string $policyName, string $modelName): string
    {
        return <<<PHP
<?php

namespace App\Policies;

use App\Models\{$modelName};
use App\Traits\Policies\Basic;

class {$policyName}
{
    use Basic;

    public function getModel()
    {
        return {$modelName}::class;
    }

    //
}
PHP;
    }
}
