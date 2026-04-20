<?php

namespace App\Traits\Filament\Specifics\Rule;

use App\Enums\Phase;
use App\Enums\Rule\Comparator;
use App\Filament\Components\Target\TargetForm;
use App\Filament\Components\Wordlist\WordlistForm;
use App\Models\Target;
use App\Rules\Rule\TargetField;
use App\Services\Datatype;
use App\Traits\Filament\Generals\Components\Field;

trait RuleField
{
    use Field, RuleButton, RuleData;

    public static function setName()
    {
        return self::textInput(
            'name',
            __('models.rule.fields.name'),
            __('forms.rule.text_examples.name')
        )
            ->helperText(__('forms.rule.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->alphaDash()
            ->required();
    }

    public static function setPhase()
    {
        return self::toggleButtons(
            'phase',
            __('models.rule.fields.phase'),
            self::phaseOptionsAndColors(),
        )
            ->helperText(fn ($state) => self::phaseDescriptions()[$state])
            ->default(Phase::One->value)
            ->required()
            ->reactive();
    }

    public static function setTarget()
    {
        $target = fn ($state) => Target::find($state);

        return self::select(
            'target_id',
            __('models.rule.fields.target'),
            [fn ($get) => new TargetField($get('phase'))]
        )
            ->helperText(__('forms.rule.descriptions.target'))
            ->required()
            ->relationship(
                'target',
                'name',
                fn ($query, $get) => $query->where('phase', $get('phase'))
            )
            ->prefix(function ($state) use ($target) {
                $target = $target($state);
                if (! $target) {
                    return null;
                }

                return self::datatypeOptionsAndColors()['options'][$target->datatype->value];
            })
            ->suffix(function ($state) use ($target) {
                $target = $target($state);
                if (! $target) {
                    return null;
                }

                return self::datatypeOptionsAndColors()['options'][Datatype::getFinal($target)];
            })
            ->afterStateUpdated(fn ($set) => $set('comparator', null))
            ->reactive()
            ->createOptionForm(TargetForm::build());
    }

    public static function setComparator()
    {
        $target = fn ($state) => Target::find($state);

        return self::select(
            'comparator',
            __('models.rule.fields.comparator'),
        )
            ->helperText(fn ($state) => self::comparatorDescriptions()[$state])
            ->options(function ($get) use ($target) {
                $target = $target($get('target_id'));
                if (! $target) {
                    return null;
                }

                return self::comparatorOptionsPerDatatype()[Datatype::getFinal($target)];
            })
            ->required()
            ->reactive();
    }

    public static function setIsInversed()
    {
        return self::toggle('is_inversed', __('models.rule.fields.is_inversed'))
            ->helperText(__('forms.rule.descriptions.is_inversed'))
            ->default(false)
            ->required();
    }

    public static function setStringValue()
    {
        $condition = fn ($get) => in_array($get('comparator'), [
            Comparator::Contains->value,
            Comparator::Match->value,
            Comparator::Mirror->value,
            Comparator::StartsWith->value,
            Comparator::EndsWith->value,
            Comparator::RegExp->value,
        ]);

        return self::textArea(
            'string_value',
            __('models.rule.extras.configurations.string_value'),
            'abcdefg123456'
        )
            ->helperText(__('forms.rule.extras.configurations.string_value'))
            ->required($condition)
            ->disabled(fn ($get) => ! $condition($get))
            ->visible($condition);
    }

    public static function setNumberValue()
    {
        $condition = fn ($get) => in_array($get('comparator'), [
            Comparator::Equal->value,
            Comparator::GreaterThan->value,
            Comparator::LessThan->value,
            Comparator::GreaterThanOrEqual->value,
            Comparator::LessThanOrEqual->value,
        ]);

        return self::textInput(
            'number_value',
            __('models.rule.extras.configurations.number_value'),
            '123456',
        )
            ->helperText(__('forms.rule.extras.configurations.number_value'))
            ->required($condition)
            ->disabled(fn ($get) => ! $condition($get))
            ->visible($condition)
            ->maxLength(null)
            ->numeric();
    }

    public static function setNumberFromValue()
    {
        $condition = fn ($get) => $get('comparator') == Comparator::InRange->value;

        return self::textInput(
            'number_from_value',
            __('models.rule.extras.configurations.number_from_value'),
            '1',
        )
            ->helperText(__('forms.rule.extras.configurations.number_from_value'))
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition)
            ->maxLength(null)
            ->lt('number_to_value')
            ->numeric();
    }

    public static function setNumberToValue()
    {
        $condition = fn ($get) => $get('comparator') == Comparator::InRange->value;

        return self::textInput(
            'number_to_value',
            __('models.rule.extras.configurations.number_to_value'),
            '10',
        )
            ->helperText(__('forms.rule.extras.configurations.number_to_value'))
            ->disabled(fn ($get) => ! $condition($get))
            ->required($condition)
            ->visible($condition)
            ->maxLength(null)
            ->gt('number_from_value')
            ->numeric();
    }

    public static function setWordlist()
    {
        $condition = fn ($get) => in_array($get('comparator'), [
            Comparator::Similar->value,
            Comparator::Search->value,
            Comparator::Check->value,
            Comparator::CheckRegExp->value,
        ]);

        return self::select('wordlist_id', __('models.rule.fields.wordlist'))
            ->helperText(__('forms.rule.descriptions.wordlist'))
            ->disabled(fn ($get) => ! $condition($get))
            ->relationship('wordlist', 'name')
            ->required($condition)
            ->visible($condition)
            ->createOptionForm(WordlistForm::build());
    }
}
