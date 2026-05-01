<?php

namespace App\Traits\Filament\Specifics\Rule;

use App\Enums\Phase;
use App\Enums\Rule\Comparator;
use App\Filament\Components\Target\TargetForm;
use App\Filament\Components\Wordlist\WordlistForm;
use App\Models\Target;
use App\Services\Datatype;
use App\Traits\Filament\Generals\Components\Field;
use App\Traits\Validators\RuleValidator;

trait RuleField
{
    use Field, RuleButton, RuleData, RuleValidator;

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
            ->required()
            ->rules(fn ($livewire) => self::validateName(ignore: $livewire->record ?? null));
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
            ->rules(self::validatePhase())
            ->reactive();
    }

    public static function setTarget()
    {
        $target = fn ($state) => Target::find($state);

        return self::select(
            'target_id',
            __('models.rule.fields.target'),
        )
            ->helperText(__('forms.rule.descriptions.target'))
            ->required()
            ->rules(fn ($get) => self::validateTarget($get('phase')))
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
            ->rules(self::validateComparator())
            ->reactive();
    }

    public static function setIsInversed()
    {
        return self::toggle('is_inversed', __('models.rule.fields.is_inversed'))
            ->helperText(__('forms.rule.descriptions.is_inversed'))
            ->default(false)
            ->required()
            ->rules(self::validateIsInversed());
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
            ->visible($condition)
            ->rules(fn ($get) => self::validateStringValue($condition($get) ? 'required' : 'nullable'));
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
            ->rules(fn ($get) => self::validateNumberValue($condition($get) ? 'required' : 'nullable'))
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
            ->rules(fn ($get) => self::validateNumberFromValue($condition($get) ? 'required' : 'nullable'))
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
            ->rules(fn ($get) => self::validateNumberToValue($condition($get) ? 'required' : 'nullable'))
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
            ->rules(fn ($get) => self::validateWordlist($condition($get) ? 'required' : 'nullable'))
            ->createOptionForm(WordlistForm::build());
    }
}
