<?php

namespace App\Filament\Components\Wordlist;

use App\Traits\Filament\Specifics\Wordlist\WordlistColumn;

class WordlistTable
{
    use WordlistColumn;

    public static function build()
    {
        return [
            self::name(),
            self::wordType(),
            self::wordCount(),
            self::labels(),
            self::createdBy(),
            self::createdAt(),
            self::updatedAt(),
        ];
    }
}
