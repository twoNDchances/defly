<?php

namespace App\Traits\Filament\Specifics\Wordlist;

use App\Traits\Filament\Generals\Components\Column;

trait WordlistColumn
{
    use Column;
    use WordlistButton;

    public static function name()
    {
        return self::textColumn('name', __('tables.columns.wordlist.name'));
    }

    public static function wordType()
    {
        return self::textColumn('word_type', __('tables.columns.wordlist.word_type'));
    }

    public static function wordCount()
    {
        return self::textColumn('word_count', __('tables.columns.wordlist.word_count'));
    }
}
