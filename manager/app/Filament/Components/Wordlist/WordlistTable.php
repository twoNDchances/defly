<?php

namespace App\Filament\Components\Wordlist;

use App\Traits\Filament\Specifics\Wordlist\WordlistColumn;

class WordlistTable
{
    use WordlistColumn;

    public static function build()
    {
        return [
            self::getName(),
            self::getType(),
            self::getWordCount(),
            self::getLabels(),
            self::getIsLocked(),
            self::getCreatedBy(),
            self::getCreatedAt(),
            self::getUpdatedAt(),
        ];
    }
}
