<?php

namespace App\Traits\Filament\Specifics\Wordlist;

use App\Enums\Wordlist\WordType;

trait WordlistData
{
    public static function wordTypeOptionsAndColors()
    {
        return [
            'options' => [
                WordType::File->value => 'File',
                WordType::Json->value => 'JSON',
            ],
            'colors' => [
                WordType::File->value => 'danger',
                WordType::Json->value => 'warning',
            ],
        ];
    }
}
