<?php

namespace App\Traits\Filament\Generals\Components;

use Filament\Schemas\Components;

trait Layout
{
    public static function grid($span = 2, $columns = 2, $schemas = [])
    {
        $grid = Components\Grid::make($columns);
        if ($span == 0) {
            $grid = $grid->columnSpanFull();
        }

        return $grid->columnSpan($span)
            ->schema($schemas);
    }

    public static function section($heading = null, $span = 2, $columns = 2, $schemas = [])
    {
        $section = Components\Section::make($heading)->collapsible();
        if ($span == 0) {
            $section = $section->columnSpanFull();
        }

        return $section->columnSpan($span)
            ->columns($columns)
            ->schema($schemas);
    }
}
