<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\GeneratesFiles;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('custom:model {name : Model class name} {--force : Overwrite existing file}')]
#[Description('Create a model class in app/Models with #[Fillable].')]
class CustomModelCommand extends Command
{
    use GeneratesFiles;

    public function handle(): int
    {
        $name = $this->normalizeStudlyName((string) $this->argument('name'));
        $name = $this->removeSuffix($name, 'Model');

        if ($name === '') {
            $this->error('Model name is required.');

            return self::FAILURE;
        }

        $path = app_path("Models/{$name}.php");
        $this->writeFile($path, $this->stub($name), (bool) $this->option('force'));

        return self::SUCCESS;
    }

    protected function stub(string $name): string
    {
        return <<<PHP
<?php

namespace App\Models;

use App\Traits\Models\Labellable;
use App\Traits\Models\Owner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable([])]
class {$name} extends Model
{
    use HasUuids, Labellable, Owner;

    //
}
PHP;
    }
}
