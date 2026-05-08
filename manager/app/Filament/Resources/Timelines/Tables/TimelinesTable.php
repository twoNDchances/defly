<?php

namespace App\Filament\Resources\Timelines\Tables;

use App\Filament\Components\Timeline\TimelineTable;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class TimelinesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(TimelineTable::build())
            ->filters([
                //
            ])
            ->recordActions([
                TimelineTable::buttonGroup(edit: false),
            ])
            ->defaultGroup(
                Group::make('created_at')
                ->date()
                ->orderQueryUsing(fn ($query) => $query->orderByDesc('created_at'))
                ->collapsible()
            )
            ->asDoubleSidedTimeline();
    }
}
