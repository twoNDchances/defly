<?php

namespace App\Traits\Filament\Specifics\Engine;

use App\Traits\Filament\Generals\Components\Column;

trait EngineColumn
{
    use Column;
    use EngineButton;
    use EngineData;

    public static function name()
    {
        return self::textColumn('name', __('tables.columns.engine.name'));
    }

    public static function inputDatatype()
    {
        return self::textColumn('input_datatype', __('tables.columns.engine.input_datatype'))
            ->formatStateUsing(fn ($state) => self::datatypeOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::datatypeOptionsAndColors()['colors'][$state->value])
            ->badge();
    }

    public static function type()
    {
        return self::textColumn('type', __('tables.columns.engine.type'))
            ->getStateUsing(fn ($record) => self::typeOptionsPerDatatypes()[$record->input_datatype->value][$record->type]);
    }

    public static function outputDatatype()
    {
        return self::textColumn('output_datatype', __('tables.columns.engine.output_datatype'))
            ->formatStateUsing(fn ($state) => self::datatypeOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::datatypeOptionsAndColors()['colors'][$state->value])
            ->badge();
    }
}
