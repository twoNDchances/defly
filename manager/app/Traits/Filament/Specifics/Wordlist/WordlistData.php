<?php

namespace App\Traits\Filament\Specifics\Wordlist;

use App\Enums\Wordlist\Type;

trait WordlistData
{
    public static function typeOptionsAndColors()
    {
        return [
            'options' => [
                Type::File->value => __('models.wordlist.extras.type.file'),
                Type::Json->value => __('models.wordlist.extras.type.json'),
            ],
            'colors' => [
                Type::File->value => 'danger',
                Type::Json->value => 'warning',
            ],
        ];
    }
}
