<?php

namespace App\Traits\Filament\Specifics\Timeline;

use App\Models\Timeline;
use App\Services\Identification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait TimelineFilter
{
    public static function buildFilters(): array
    {
        return [
            SelectFilter::make('created_by')
                ->label(__('tables.timeline.filters.user'))
                ->relationship(
                    'createdBy',
                    'email',
                    modifyQueryUsing: fn (Builder $query): Builder => $query
                        ->whereHas('getTimelines'),
                    hasEmptyOption: true,
                )
                ->emptyRelationshipOptionLabel(__('tables.timeline.filters.system'))
                ->searchable()
                ->preload()
                ->visible(fn (): bool => Identification::isRoot()),
            SelectFilter::make('resource_type')
                ->label(__('tables.timeline.filters.resource'))
                ->options(fn (): array => Timeline::query()
                    ->whereNotNull('resource_type')
                    ->distinct()
                    ->orderBy('resource_type')
                    ->pluck('resource_type')
                    ->mapWithKeys(fn (string $resourceType): array => [
                        $resourceType => self::resourceTypeOptionsAndColors()['options'][$resourceType]
                            ?? Str::headline(class_basename($resourceType)),
                    ])
                    ->all())
                ->searchable()
                ->visible(fn (): bool => Identification::isRoot()),
            SelectFilter::make('action')
                ->label(__('tables.timeline.filters.action'))
                ->options(fn (): array => Timeline::query()
                    ->whereNotNull('action')
                    ->distinct()
                    ->orderBy('action')
                    ->pluck('action')
                    ->mapWithKeys(fn (string $action): array => [
                        $action => self::actionOptionsAndColors()['options'][$action]
                            ?? Str::headline($action),
                    ])
                    ->all())
                ->visible(fn (): bool => Identification::isRoot()),
            SelectFilter::make('method')
                ->label(__('tables.timeline.filters.method'))
                ->options(self::methodOptionsAndColors()['options'])
                ->visible(fn (): bool => Identification::isRoot()),
            Filter::make('created_at')
                ->label(__('tables.timeline.filters.period'))
                ->schema([
                    DatePicker::make('from')
                        ->label(__('tables.timeline.filters.from')),
                    DatePicker::make('until')
                        ->label(__('tables.timeline.filters.until')),
                ])
                ->columns(2)
                ->query(fn (Builder $query, array $data): Builder => $query
                    ->when(
                        $data['from'] ?? null,
                        fn (Builder $query, string $date): Builder => $query
                            ->whereDate('created_at', '>=', $date),
                    )
                    ->when(
                        $data['until'] ?? null,
                        fn (Builder $query, string $date): Builder => $query
                            ->whereDate('created_at', '<=', $date),
                    ))
                ->indicateUsing(function (array $data): array {
                    $indicators = [];

                    if ($data['from'] ?? null) {
                        $indicators[] = Indicator::make(__('tables.timeline.filters.indicators.from', [
                            'date' => $data['from'],
                        ]))->removeField('from');
                    }

                    if ($data['until'] ?? null) {
                        $indicators[] = Indicator::make(__('tables.timeline.filters.indicators.until', [
                            'date' => $data['until'],
                        ]))->removeField('until');
                    }

                    return $indicators;
                })
                ->visible(fn (): bool => Identification::isRoot()),
        ];
    }
}
