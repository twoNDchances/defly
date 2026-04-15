<?php

namespace App\Traits\Filament\Generals\Components;

use Filament\Support\Colors\Color;
use Filament\Tables\Columns;

trait Column
{
    public static function textColumn(string $name, $label = null)
    {
        return Columns\TextColumn::make($name)
            ->label($label)
            ->searchable()
            ->toggleable()
            ->sortable();
    }

    public static function booleanColumn(string $name, $label = null)
    {
        return Columns\IconColumn::make($name)
            ->label($label)
            ->searchable()
            ->toggleable()
            ->sortable()
            ->boolean();
    }

    public static function relationshipColumn(string $name, $label = null)
    {
        return self::textColumn($name, $label)
            ->expandableLimitedList()
            ->listWithLineBreaks()
            ->limitList(5)
            ->bulleted();
    }

    public static function colorColumn(string $name, $label = null)
    {
        return Columns\ColorColumn::make($name)
            ->label($label)
            ->searchable()
            ->toggleable()
            ->sortable();
    }

    public static function getLabels()
    {
        return self::relationshipColumn('labels.name', __('models.label.name'))
            ->color(fn ($state, $record) => Color::hex($record->labels()->pluck('color', 'name')->toArray()[$state]))
            ->bulleted(false)
            ->badge();
    }

    public static function getLocked()
    {
        return self::booleanColumn('locked', __('models.generals.specials.locked'));
    }

    public static function getCreatedBy()
    {
        return self::textColumn('createdBy.email', __('models.generals.bases.created_by'))
            ->badge();
    }

    public static function datetimeColumn(string $name, $label = null)
    {
        return self::textColumn($name, $label)
            ->dateTime();
    }

    public static function getCreatedAt()
    {
        return self::datetimeColumn('created_at', __('models.generals.bases.created_at'))
            ->toggledHiddenByDefault();
    }

    public static function getUpdatedAt()
    {
        return self::datetimeColumn('updated_at', __('models.generals.bases.updated_at'))
            ->toggledHiddenByDefault();
    }
}
