<?php

namespace App\Filament\Resources\Timelines\Tables;

use App\Filament\Components\Timeline\TimelineTable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
            ->defaultGroup(Group::make('created_at')->date())
            ->recordActions([
                TimelineTable::buttonGroup(edit: false),
            ])
            ->toolbarActions([
                TimelineTable::bulkButtonGroup(),
            ])
            ->poll('5s')
            ->asDoubleSidedTimeline();
    }
}
