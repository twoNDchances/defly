<?php

namespace App\Traits\Filament\Specifics\Wordlist;

use App\Enums\Wordlist\Type;
use App\Services\Logger;
use App\Traits\Filament\Generals\Components\Button;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait WordlistButton
{
    use Button;

    public static function cloneWordlistButton()
    {
        return self::cloneButton()
            ->action(function ($record) {
                $clone = $record->replicate();
                $suffix = Str::random(6);
                $clone->name = "$record->name-$suffix";
                $clone->is_locked = false;

                if ($record->type === Type::File) {
                    if (isset($record->word_file) && Storage::exists($record->word_file)) {
                        $clone->word_file = preg_replace('/(\.[^\.]+)$/', "-{$suffix}$1", $record->word_file);
                        Storage::copy($record->word_file, $clone->word_file);
                    } else {
                        $clone->word_file = null;
                    }
                }

                $clone->save();
                $clone->labels()->sync($record->labels()->pluck('id')->all());
                Logger::log($record, 'clone');
            });
    }
}
