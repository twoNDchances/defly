<?php

namespace App\Traits\Filament\Specifics\Label;

use App\Traits\Filament\Generals\Components\Column;
use Filament\Support\Colors\Color;

trait LabelColumn
{
    use Column, LabelButton, LabelData;

    public static function getName()
    {
        return self::textColumn('name', __('models.label.fields.name'));
    }

    public static function getColor()
    {
        return self::colorColumn('color', __('models.label.fields.color'));
    }

    public static function getPreview()
    {
        return self::textColumn('preview', __('tables.label.preview'))
            ->getStateUsing(fn ($record) => $record->name)
            ->color(fn ($record) => Color::hex($record->color))
            ->badge();
    }
}
