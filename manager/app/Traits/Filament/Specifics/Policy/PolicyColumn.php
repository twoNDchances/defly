<?php

namespace App\Traits\Filament\Specifics\Policy;

use App\Traits\Filament\Generals\Components\Column;

trait PolicyColumn
{
    use Column, PolicyButton, PolicyData;

    public static function getName()
    {
        return self::textColumn('name');
    }

    public static function getLevel()
    {
        return self::textColumn('level')
            ->numeric();
    }

    public static function getPhase()
    {
        return self::textColumn('phase', __('models.policy.fields.phase'))
            ->formatStateUsing(fn ($state) => self::phaseOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::phaseOptionsAndColors()['colors'][$state->value])
            ->badge();
    }

    public static function getValidationStatus()
    {
        return self::textColumn('validation_status', __('models.policy.fields.validation_status'))
            ->formatStateUsing(fn ($state) => self::validationStatusOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::validationStatusOptionsAndColors()['colors'][$state->value])
            ->badge();
    }

    public static function getRules()
    {
        return self::relationshipColumn('rules.name', __('tables.policy.rules'));
    }

    public static function getDefenders()
    {
        return self::relationshipColumn('defenders.name', __('tables.policy.defenders'));
    }
}
