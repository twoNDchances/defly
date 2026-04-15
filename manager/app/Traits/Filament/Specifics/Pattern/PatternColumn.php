<?php

namespace App\Traits\Filament\Specifics\Pattern;

use App\Traits\Filament\Generals\Components\Column;

trait PatternColumn
{
    use Column, PatternButton, PatternData;

    public static function getName()
    {
        return self::textColumn('name', __('models.pattern.fields.name'));
    }

    public static function getPhase()
    {
        return self::textColumn('phase', __('models.pattern.fields.phase'))
            ->formatStateUsing(fn ($state) => self::phaseOptionsAndColors()['options'][$state])
            ->color(fn ($state) => self::phaseOptionsAndColors()['colors'][$state])
            ->badge();
    }

    public static function getType()
    {
        return self::textColumn('type', __('models.pattern.fields.type'))
            ->formatStateUsing(fn ($state) => self::typeOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::typeOptionsAndColors()['colors'][$state->value])
            ->badge();
    }

    public static function getDatatype()
    {
        return self::textColumn('datatype', __('models.pattern.fields.datatype'))
            ->formatStateUsing(fn ($state) => self::datatypeOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::datatypeOptionsAndColors()['colors'][$state->value])
            ->badge();
    }

    public static function getTargets()
    {
        return self::relationshipColumn('targets.name', __('models.pattern.fields.targets'));
    }
}
