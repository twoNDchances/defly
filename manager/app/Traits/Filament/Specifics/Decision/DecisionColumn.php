<?php

namespace App\Traits\Filament\Specifics\Decision;

use App\Traits\Filament\Generals\Components\Column;

trait DecisionColumn
{
    use Column, DecisionButton, DecisionData;

    public static function getName()
    {
        return self::textColumn('name', __('models.decision.fields.name'));
    }

    public static function getDirection()
    {
        return self::textColumn('direction', __('models.decision.fields.direction'))
            ->formatStateUsing(fn ($state) => self::directionOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::directionOptionsAndColors()['colors'][$state->value])
            ->badge();
    }

    public static function getCondition()
    {
        return self::textColumn('condition', __('models.decision.fields.condition'))
            ->formatStateUsing(fn ($state) => self::conditionOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::conditionOptionsAndColors()['colors'][$state->value])
            ->badge();
    }

    public static function getScore()
    {
        return self::textColumn('score', __('models.decision.fields.score'))->numeric();
    }

    public static function getAction()
    {
        return self::textColumn('action', __('models.decision.fields.action'))
            ->formatStateUsing(fn ($record, $state) => self::actionOptionsPerDirection()[$record->direction->value][$state->value]);
    }

    public static function getDefenders()
    {
        return self::relationshipColumn('defenders.name', __('tables.decision.defenders'));
    }

    public static function getIsImplemented()
    {
        return self::booleanColumn('pivot.is_implemented', __('tables.decision.is_implemented'));
    }
}
