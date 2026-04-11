<?php

namespace App\Traits\Filament\Specifics\Label;

use App\Traits\Filament\Generals\Components\Column;
use Filament\Support\Colors\Color;

trait LabelColumn
{
    use Column, LabelButton, LabelData;

    public static function name()
    {
        return self::textColumn('name', __('tables.columns.label.name'));
    }

    public static function color()
    {
        return self::colorColumn('color', __('tables.columns.label.color'));
    }

    public static function preview()
    {
        return self::textColumn('preview', __('tables.columns.label.preview'))
            ->getStateUsing(fn ($record) => $record->name)
            ->color(fn ($record) => Color::hex($record->color))
            ->badge();
    }
}
