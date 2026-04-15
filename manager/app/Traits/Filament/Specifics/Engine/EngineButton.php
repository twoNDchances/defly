<?php

namespace App\Traits\Filament\Specifics\Engine;

use App\Models\Engine;
use App\Traits\Filament\Generals\Components\Button;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

trait EngineButton
{
    use Button;

    public static function cloneButton()
    {
        return self::button(
            'clone_engine',
            'Clone',
            Heroicon::OutlinedSquare2Stack,
            function (Engine $record): void {
                $clone = $record->replicate();
                $clone->name = self::generateCloneName($record->name);
                $clone->locked = false;
                $clone->save();
                $clone->labels()->sync($record->labels()->pluck('id')->all());
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

        while (Engine::query()->where('name', $candidate)->exists()) {
            $candidate = "{$base}-{$index}";
            $index++;
        }

        return $candidate;
    }
}
