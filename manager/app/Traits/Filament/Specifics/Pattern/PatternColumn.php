<?php

namespace App\Traits\Filament\Specifics\Pattern;

use App\Traits\Filament\Generals\Components\Column;

trait PatternColumn
{
    use Column, PatternButton, PatternData;

    public static function name()
    {
        return self::textColumn('name', __('tables.columns.pattern.name'));
    }

    public static function phase()
    {
        return self::textColumn('phase', __('tables.columns.pattern.phase'))
        ->formatStateUsing(fn ($state) => self::phaseOptionsAndColors()['options'][$state])
        ->color(fn ($state) => self::phaseOptionsAndColors()['colors'][$state])
        ->badge();
    }

    public static function type()
    {
        return self::textColumn('type', __('tables.columns.pattern.type'))
        ->formatStateUsing(fn ($state) => self::typeOptionsAndColors()['options'][$state->value])
        ->color(fn ($state) => self::typeOptionsAndColors()['colors'][$state->value])
        ->badge();
    }

    public static function datatype()
    {
        return self::textColumn('datatype', __('tables.columns.pattern.datatype'))
        ->formatStateUsing(fn ($state) => self::datatypeOptionsAndColors()['options'][$state->value])
        ->color(fn ($state) => self::datatypeOptionsAndColors()['colors'][$state->value])
        ->badge();
    }

    public static function targets()
    {
        return self::relationshipColumn('targets.name', __('tables.columns.pattern.targets'));
    }
}
