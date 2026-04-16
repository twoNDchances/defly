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

    // public static function getUsers()
    // {
    //     return self::relationshipColumn('users.email', __('models.user.name'));
    // }

    // public static function getGroups()
    // {
    //     return self::relationshipColumn('groups.name', __('models.group.name'));
    // }

    // public static function getPermissions()
    // {
    //     return self::relationshipColumn('permissions.name', __('models.permission.name'));
    // }

    // public static function getWordlists()
    // {
    //     return self::relationshipColumn('wordlists.name', __('models.wordlist.name'));
    // }

    // public static function getEngines()
    // {
    //     return self::relationshipColumn('engines.name', __('models.engine.name'));
    // }

    // public static function getTarget()
    // {
    //     return self::relationshipColumn('targets.name', __('models.target.name'));
    // }
}
