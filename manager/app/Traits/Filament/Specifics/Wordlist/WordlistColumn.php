<?php

namespace App\Traits\Filament\Specifics\Wordlist;

use App\Traits\Filament\Generals\Components\Column;

trait WordlistColumn
{
    use Column, WordlistButton, WordlistData;

    public static function getName()
    {
        return self::textColumn('name', __('models.wordlist.fields.name'));
    }

    public static function getType()
    {
        return self::textColumn('type', __('models.wordlist.fields.type'))
            ->formatStateUsing(fn ($state) => self::typeOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::typeOptionsAndColors()['colors'][$state->value])
            ->badge();
    }

    public static function getWordCount()
    {
        return self::textColumn('word_count', __('models.wordlist.fields.word_count'));
    }
}
