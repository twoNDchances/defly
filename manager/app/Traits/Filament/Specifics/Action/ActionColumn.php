<?php

namespace App\Traits\Filament\Specifics\Action;

use App\Traits\Filament\Generals\Components\Column;

trait ActionColumn
{
    use ActionButton, ActionData, Column;

    public static function getName()
    {
        return self::textColumn('name', __('models.action.fields.name'));
    }

    public static function getType()
    {
        return self::textColumn('type', __('models.action.fields.type'))
            ->formatStateUsing(fn ($state) => self::typeOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::typeOptionsAndColors()['colors'][$state->value])
            ->badge();
    }
}
