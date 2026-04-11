<?php

namespace App\Console\Commands\Concerns;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait GeneratesFiles
{
    protected function normalizeStudlyName(string $name): string
    {
        $normalized = str_replace(['\\', '/'], ' ', trim($name));

        return Str::studly($normalized);
    }

    protected function removeSuffix(string $name, string $suffix): string
    {
        return Str::endsWith($name, $suffix)
            ? Str::beforeLast($name, $suffix)
            : $name;
    }

    protected function writeFile(string $path, string $contents, bool $force = false): bool
    {
        $exists = File::exists($path);
        if ($exists && ! $force) {
            $this->warn("Skipped existing file: {$path}");

            return false;
        }

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $contents);

        $this->info(($exists ? 'Updated file: ' : 'Created file: ').$path);

        return true;
    }

    protected function ensureDirectory(string $path): void
    {
        if (File::isDirectory($path)) {
            return;
        }

        File::makeDirectory($path, 0755, true);
        $this->info("Created directory: {$path}");
    }
}
