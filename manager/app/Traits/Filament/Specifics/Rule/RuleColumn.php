<?php

namespace App\Traits\Filament\Specifics\Rule;

use App\Traits\Filament\Generals\Components\Column;

trait RuleColumn
{
    use Column, RuleButton, RuleData;

    public static function getName()
    {
        return self::textColumn('name', __('models.rule.fields.name'));
    }

    public static function getPhase()
    {
        return self::textColumn('phase', __('models.rule.fields.phase'))
            ->formatStateUsing(fn ($state) => self::phaseOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::phaseOptionsAndColors()['colors'][$state->value])
            ->badge();
    }

    public static function getTarget()
    {
        return self::relationshipColumn('target.name', __('models.rule.fields.target'));
    }

    public static function getComparator()
    {
        return self::textColumn('comparator', __('models.rule.fields.comparator'))
            ->formatStateUsing(fn ($record) => self::comparatorOptions()[$record->comparator->value]);
    }

    public static function getWordlist()
    {
        return self::relationshipColumn('wordlist.name', __('models.rule.fields.wordlist'));
    }

    public static function getActions()
    {
        return self::relationshipColumn('actions.name', __('tables.rule.actions'));
    }

    public static function getPolicies()
    {
        return self::relationshipColumn('policies.name', __('tables.rule.policies'));
    }
}
