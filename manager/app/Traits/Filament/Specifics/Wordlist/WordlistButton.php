<?php

namespace App\Traits\Filament\Specifics\Wordlist;

use App\Enums\Wordlist\Type;
use App\Models\Wordlist;
use App\Traits\Filament\Generals\Components\Button;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait WordlistButton
{
    use Button;

    public static function cloneButton()
    {
        return self::button(
            'clone_wordlist',
            'Clone',
            Heroicon::OutlinedSquare2Stack,
            function (Wordlist $record): void {
                $clone = $record->replicate();
                $clone->name = self::generateCloneName($record->name);
                $clone->locked = false;

                if ($record->type === Type::File && filled($record->word_file)) {
                    if (Storage::exists($record->word_file)) {
                        $extension = pathinfo($record->word_file, PATHINFO_EXTENSION);
                        $directory = pathinfo($record->word_file, PATHINFO_DIRNAME);
                        $filename = Str::uuid()->toString() . ($extension ? ".{$extension}" : '');
                        $directory = $directory === '.' ? '' : trim($directory, '/\\');
                        $clone->word_file = ($directory === '' ? '' : "{$directory}/") . $filename;
                        Storage::copy($record->word_file, $clone->word_file);
                    } else {
                        $clone->word_file = null;
                    }
                }

                $clone->save();
                $clone->labels()->sync($record->labels()->pluck('id')->all());
                $clone->update(['locked' => $clone->targets()->exists() || $clone->labels()->exists()]);
            },
        )
            ->requiresConfirmation()
            ->color('gray');
    }

    public static function deleteUnlockedBulkButton()
    {
        return self::deleteBulkButton()
            ->action(function ($records): void {
                foreach ($records as $record) {
                    if ($record->locked === false) {
                        $record->delete();
                    }
                }
            });
    }

    protected static function generateCloneName(string $name): string
    {
        $base = Str::finish($name, '-clone');
        $candidate = $base;
        $index = 2;

        while (Wordlist::query()->where('name', $candidate)->exists()) {
            $candidate = "{$base}-{$index}";
            $index++;
        }

        return $candidate;
    }
}
