<?php

namespace App\Filament\Resources\Timelines\Tables;

use App\Filament\Components\Timeline\TimelineTable;
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
            ->toolbarActions([
                TimelineTable::bulkButtonGroup(),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('5s');
    }
}
