<?php

namespace App\Filament\Resources\Timelines;

use App\Filament\Resources\Timelines\Pages\CreateTimeline;
use App\Filament\Resources\Timelines\Pages\EditTimeline;
use App\Filament\Resources\Timelines\Pages\ListTimelines;
use App\Filament\Resources\Timelines\Schemas\TimelineForm;
use App\Filament\Resources\Timelines\Tables\TimelinesTable;
use App\Models\Timeline;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TimelineResource extends Resource
{
    protected static ?string $model = Timeline::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return TimelineForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TimelinesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTimelines::route('/'),
            'create' => CreateTimeline::route('/create'),
            'edit' => EditTimeline::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('navigations.groups.utilities');
    }

    public static function getModelLabel(): string
    {
        return __('models.timeline.name');
    }
}
