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

    public static function labels()
    {
        return self::relationshipColumn('labels.name', __('tables.commons.labels'))
            ->color(fn ($state, $record) => Color::hex($record->labels()->pluck('color', 'name')->toArray()[$state]))
            ->bulleted(false)
            ->badge();
    }

    public static function createdBy()
    {
        return self::textColumn('createdBy.email', __('tables.commons.created_by'))
            ->badge();
    }

    public static function datetimeColumn(string $name, $label = null)
    {
        return self::textColumn($name, $label)
            ->dateTime();
    }

    public static function createdAt()
    {
        return self::datetimeColumn('created_at', __('tables.commons.created_at'))
            ->toggledHiddenByDefault();
    }

    public static function updatedAt()
    {
        return self::datetimeColumn('updated_at', __('tables.commons.updated_at'))
            ->toggledHiddenByDefault();
    }
}
