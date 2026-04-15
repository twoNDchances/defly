<?php

namespace App\Traits\Filament\Specifics\Engine;

use App\Traits\Filament\Generals\Components\Column;

trait EngineColumn
{
    use Column, EngineButton, EngineData;

    public static function getName()
    {
        return self::textColumn('name', __('models.engine.fields.name'));
    }

    public static function getInputDatatype()
    {
        return self::textColumn('input_datatype', __('models.engine.fields.input_datatype'))
            ->formatStateUsing(fn ($state) => self::datatypeOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::datatypeOptionsAndColors()['colors'][$state->value])
            ->badge();
    }

    public static function getType()
    {
        return self::textColumn('type', __('models.engine.fields.type'))
            ->getStateUsing(fn ($record) => self::typeOptionsPerDatatypes()[$record->input_datatype->value][$record->type]);
    }

    public static function getOutputDatatype()
    {
        return self::textColumn('output_datatype', __('models.engine.fields.output_datatype'))
            ->formatStateUsing(fn ($state) => self::datatypeOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::datatypeOptionsAndColors()['colors'][$state->value])
            ->badge();
    }
}
