<?php

namespace App\Filament\Resources\Timelines\Tables;

use App\Filament\Components\Timeline\TimelineTable;
use App\Services\Identification;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TimelinesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(TimelineTable::build())
            ->modifyQueryUsing(fn (Builder $query): Builder => Identification::isRoot()
                ? $query
                : $query->where('created_by', Identification::getId()))
            ->filters(TimelineTable::buildFilters())
            ->filtersFormColumns(2)
            ->recordActions([
                TimelineTable::buttonGroup(edit: false),
            ])
            ->toolbarActions([
                TimelineTable::bulkButtonGroup(false, [TimelineTable::deleteTimelineBulkButton()]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
