<?php

namespace App\Traits\Filament\Specifics\Wordlist;

use App\Enums\Wordlist\Type;
use App\Traits\Filament\Generals\Components\Field;
use App\Traits\Validators\WordlistValidator;

trait WordlistField
{
    use Field, WordlistButton, WordlistData, WordlistValidator;

    public static function setName()
    {
        return self::textInput(
            'name',
            __('models.wordlist.fields.name'),
            __('forms.wordlist.text_examples.name'),
        )
            ->helperText(__('forms.wordlist.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->alphaDash()
            ->required()
            ->rules(fn ($livewire) => self::validateName(ignore: $livewire->record ?? null));
    }

    public static function setType()
    {
        return self::toggleButtons('type', __('models.wordlist.fields.type'), self::typeOptionsAndColors())
            ->default(Type::File->value)
            ->reactive()
            ->required()
            ->rules(self::validateType());
    }

    public static function setWordFile()
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
            ->rules(fn ($get) => self::validateWordFile($condition($get) ? 'required' : 'nullable'))
            ->mimeTypeMap(['text/plain']);
    }

    public static function setWordJson()
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
                    ->rules(self::validateWordJsonWord())
                    ->columnSpanFull(),
            ],
        )
            ->helperText(__('forms.wordlist.descriptions.word_json'))
            ->required($condition)
            ->disabled(fn ($get) => ! $condition($get))
            ->visible($condition)
            ->rules(fn ($get) => self::validateWordJson($condition($get) ? 'required' : 'nullable'))
            ->minItems(1);
    }

    public static function setDescriptionField()
    {
        return self::setDescription();
    }
}
