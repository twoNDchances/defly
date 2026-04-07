<?php

namespace App\Traits\Filament\Specifics\Wordlist;

use App\Enums\Wordlist\Type;
use App\Traits\Filament\Generals\Components\Field;

trait WordlistField
{
    use Field;
    use WordlistButton;
    use WordlistData;

    public static function name()
    {
        return self::textInput(
            'name',
            __('models.wordlist.fields.name'),
            __('forms.wordlist.text_examples.name'),
        )
            ->helperText(__('forms.wordlist.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->alphaDash()
            ->required();
    }

    public static function type()
    {
        return self::toggleButtons('type', __('models.wordlist.fields.type'), self::typeOptionsAndColors())
            ->default(Type::File->value)
            ->reactive()
            ->required();
    }

    public static function wordFile()
    {
        $condition = fn ($get) => $get('type') == Type::File->value;

        return self::fileUpload(
            'word_file',
            __('models.wordlist.fields.word_file'),
            'wordlists',
        )
            ->disk('')
            ->helperText(__('forms.wordlist.descriptions.word_file'))
            ->required($condition)
            ->disabled(fn ($get) => ! $condition($get))
            ->visible($condition)
            ->mimeTypeMap(['text/plain']);
    }

    public static function wordJson()
    {
        $condition = fn ($get) => $get('type') == Type::Json->value;

        return self::repeater(
            'word_json',
            __('models.wordlist.fields.word_json'),
            'word',
            [
                self::textInput(
                    'word',
                    __('models.wordlist.extras.word'),
                    __('forms.wordlist.text_examples.word'),
                )
                    ->helperText(__('forms.wordlist.descriptions.word'))
                    ->required()
                    ->columnSpanFull(),
            ],
        )
            ->helperText(__('forms.wordlist.descriptions.word_json'))
            ->required($condition)
            ->disabled(fn ($get) => ! $condition($get))
            ->visible($condition)
            ->minItems(1);
    }

    public static function description()
    {
        return self::textArea(
            'description',
            __('models.commons.description'),
            __('forms.wordlist.text_examples.description'),
        )
            ->helperText(__('forms.wordlist.descriptions.description'));
    }
}
