<?php

namespace App\Filament\Resources\Timelines\Pages;

use App\Filament\Resources\Timelines\TimelineResource;
use App\Services\Identification;
use App\Traits\Filament\Generals\Pages\ListPage;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListTimelines extends ListRecords
{
    use ListPage;

    protected static string $resource = TimelineResource::class;

    public function getTabs(): array
    {
        if (! Identification::isRoot()) {
            return [];
        }

        return [
            'mine' => Tab::make(__('tables.timeline.tabs.mine'))
                ->icon(Heroicon::OutlinedUserCircle)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('created_by', Identification::getId())),
            'all' => Tab::make(__('tables.timeline.tabs.all'))
                ->icon(Heroicon::OutlinedGlobeAlt),
        ];
    }
}
