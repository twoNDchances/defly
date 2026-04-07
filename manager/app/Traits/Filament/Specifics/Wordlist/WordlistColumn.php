<?php

namespace App\Traits\Filament\Specifics\Wordlist;

use App\Traits\Filament\Generals\Components\Column;

trait WordlistColumn
{
    use Column;
    use WordlistButton;
    use WordlistData;

    public static function name()
    {
        return self::textColumn('name', __('tables.columns.wordlist.name'));
    }

    public static function type()
    {
        return self::textColumn('type', __('tables.columns.wordlist.type'))
            ->formatStateUsing(fn ($state) => self::typeOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::typeOptionsAndColors()['colors'][$state->value])
            ->badge();
    }

    public static function wordCount()
    {
        return self::textColumn('word_count', __('tables.columns.wordlist.word_count'));
    }
}
