<?php

namespace App\Traits\Filament\Specifics\Target;

use App\Traits\Filament\Generals\Components\Column;

trait TargetColumn
{
    use Column, TargetButton, TargetData;

    public static function name()
    {
        return self::textColumn('name', __('tables.columns.target.name'));
    }

    public static function phase()
    {
        return self::textColumn('phase', __('tables.columns.target.phase'))
            ->formatStateUsing(fn ($state) => self::phaseOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::phaseOptionsAndColors()['colors'][$state->value])
            ->badge();
    }

    public static function type()
    {
        return self::textColumn('type', __('tables.columns.target.type'))
            ->formatStateUsing(fn ($state) => self::typeOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::typeOptionsAndColors()['colors'][$state->value])
            ->badge();
    }

    public static function datatype()
    {
        return self::textColumn('datatype', __('tables.columns.target.datatype'))
            ->formatStateUsing(fn ($state) => self::datatypeOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::datatypeOptionsAndColors()['colors'][$state->value])
            ->badge();
    }

    public static function pattern()
    {
        return self::relationshipColumn('pattern.name', __('tables.columns.target.pattern'));
    }

    public static function wordlist()
    {
        return self::relationshipColumn('wordlist.name', __('tables.columns.target.wordlist'));
    }
}
