<?php

namespace App\Traits\Filament\Specifics\Target;

use App\Enums\Datatype;
use App\Enums\Phase;
use App\Enums\Type;
use App\Filament\Components\Wordlist\WordlistForm;
use App\Models\Pattern;
use App\Traits\Filament\Generals\Components\Field;
use App\Traits\Validators\TargetValidator;

trait TargetField
{
    use Field, TargetButton, TargetData, TargetValidator;

    public static function setPhase()
    {
        return self::toggleButtons('phase', __('models.target.fields.phase'), self::phaseOptionsAndColors())
            ->helperText(fn ($state) => self::phaseDescriptions()[$state])
            ->afterStateUpdated(fn ($set) => [$set('type', Type::Getter->value), $set('pattern', null)])
            ->default(Phase::One->value)
            ->reactive()
            ->required()
            ->rules(self::validatePhase());
    }

    public static function setType()
    {
        return self::toggleButtons('type', __('models.target.fields.type'))
            ->helperText(fn ($state) => self::typeDescriptions()[$state])
            ->options(fn ($get) => self::typeOptionsAndColorsPerPhases()[$get('phase')]['options'])
            ->colors(fn ($get) => self::typeOptionsAndColorsPerPhases()[$get('phase')]['colors'])
            ->afterStateUpdated(fn ($set) => $set('pattern', null))
            ->default(Type::Getter->value)
            ->reactive()
            ->required()
            ->rules(self::validateType());
    }

    public static function setPattern()
    {
        $condition = fn ($get) => $get('type') == Type::Getter->value;

        return self::select('pattern', __('models.target.fields.pattern'))
            ->helperText(__('forms.target.descriptions.pattern'))
            ->relationship(
                'pattern',
                'name',
                fn ($query, $get) => $query->where('phase', $get('phase'))->where('type', $get('type')),
            )
            ->afterStateUpdated(fn ($state, $set) => $set('datatype', Pattern::find($state)?->datatype))
            ->disabled($condition)
            ->required(fn ($get) => $get('type') == Type::Full->value)
            ->visible(fn ($get) => ! $condition($get))
            ->reactive();
    }

    public static function setName()
    {
        return self::textInput(
            'name',
            __('models.target.fields.name'),
            __('forms.target.text_examples.name'),
        )
            ->helperText(__('forms.target.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->alphaDash()
            ->required();
    }

    public static function setDatatype()
    {
        return self::toggleButtons('datatype', __('models.target.fields.datatype'), self::datatypeOptionsAndColors())
            ->helperText(fn ($state) => self::datatypeDescriptions()[$state])
            ->disabled(fn ($get) => $get('pattern'))
            ->default(Datatype::Array->value)
            ->reactive()
            ->required();
    }

    public static function setWordlist()
    {
        $condition = fn ($get) => $get('datatype') == Datatype::Array->value && ! $get('pattern');

        return self::select('wordlist', __('models.target.fields.wordlist'))
            ->helperText(__('forms.target.descriptions.wordlist'))
            ->disabled(fn ($get) => ! $condition($get))
            ->relationship('wordlist', 'name')
            ->required($condition)
            ->visible($condition)
            ->createOptionForm(WordlistForm::build());
    }

    public static function setDescriptionField()
    {
        return self::setDescription();
    }
}
