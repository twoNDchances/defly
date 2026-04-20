<?php

namespace App\Traits\Filament\Specifics\Target;

use App\Traits\Filament\Generals\Components\Column;

trait TargetColumn
{
    use Column, TargetButton, TargetData;

    public static function getName()
    {
        return self::textColumn('name', __('models.target.fields.name'));
    }

    public static function getPhase()
    {
        return self::textColumn('phase', __('models.target.fields.phase'))
            ->formatStateUsing(fn ($state) => self::phaseOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::phaseOptionsAndColors()['colors'][$state->value])
            ->badge();
    }

    public static function getType()
    {
        return self::textColumn('type', __('models.target.fields.type'))
            ->formatStateUsing(fn ($state) => self::typeOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::typeOptionsAndColors()['colors'][$state->value])
            ->badge();
    }

    public static function getDatatype()
    {
        return self::textColumn('datatype', __('models.target.fields.datatype'))
            ->formatStateUsing(fn ($state) => self::datatypeOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::datatypeOptionsAndColors()['colors'][$state->value])
            ->badge();
    }

    public static function getPattern()
    {
        return self::relationshipColumn('pattern.name', __('models.target.fields.pattern'));
    }

    public static function getWordlist()
    {
        return self::relationshipColumn('wordlist.name', __('models.target.fields.wordlist'));
    }

    public static function getEngines()
    {
        return self::relationshipColumn('engines.name', __('tables.target.engines'));
    }

    public static function getRules()
    {
        return self::relationshipColumn('rules.name', __('tables.target.rules'));
    }
}
