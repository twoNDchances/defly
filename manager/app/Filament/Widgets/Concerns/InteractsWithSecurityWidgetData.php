<?php

namespace App\Filament\Widgets\Concerns;

use App\Models\Defender;
use App\Models\Principle;
use App\Models\Report;
use App\Models\Timeline;
use App\Services\Identification;
use App\Services\Security;
use BackedEnum;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Stringable;
use UnitEnum;

trait InteractsWithSecurityWidgetData
{
    protected function currentDefender(): ?Defender
    {
        $record = $this->record ?? null;

        return $record instanceof Defender ? $record : null;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function emptyQuery(string $modelClass): Builder
    {
        return $modelClass::query()->whereKey([]);
    }

    protected function defendersQuery(): Builder
    {
        return Security::can(Defender::class, 'viewAny')
            ? Defender::query()->visibleTo(Identification::getCurrent())
            : $this->emptyQuery(Defender::class);
    }

    protected function principlesQuery(): Builder
    {
        return Security::can(Principle::class, 'viewAny')
            ? Principle::query()
            : $this->emptyQuery(Principle::class);
    }

    protected function reportsQuery(?Defender $defender = null): Builder
    {
        if (! Security::can(Report::class, 'viewAny')) {
            return $this->emptyQuery(Report::class);
        }

        return Report::query()
            ->when($defender, fn (Builder $query): Builder => $query->where('reports.created_by', $defender->getKey()));
    }

    protected function timelinesQuery(?Defender $defender = null): Builder
    {
        if (! Security::can(Timeline::class, 'viewAny')) {
            return $this->emptyQuery(Timeline::class);
        }

        return Timeline::query()
            ->when($defender, fn (Builder $query): Builder => $query
                ->where('resource_type', Defender::class)
                ->where('resource_id', $defender->getKey()));
    }

    protected function getFilters(): ?array
    {
        if (! $this->hasSecurityDateFilter()) {
            return null;
        }

        $this->filter ??= $this->defaultSecurityDateFilter();

        return $this->securityDateFilterOptions();
    }

    protected function hasSecurityDateFilter(): bool
    {
        return (bool) ($this->hasSecurityDateFilter ?? false);
    }

    protected function allowsAllSecurityDateFilter(): bool
    {
        return (bool) ($this->allowsAllSecurityDateFilter ?? false);
    }

    protected function defaultSecurityDateFilter(): string
    {
        $default = (string) ($this->defaultSecurityDateFilter ?? ($this->allowsAllSecurityDateFilter() ? 'all' : '14'));
        $options = $this->securityDateFilterOptions();

        return array_key_exists($default, $options) ? $default : (string) array_key_first($options);
    }

    protected function selectedSecurityDateFilterDays(): ?int
    {
        $filter = (string) ($this->filter ?? $this->defaultSecurityDateFilter());

        if ($filter === 'all' && $this->allowsAllSecurityDateFilter()) {
            return null;
        }

        $days = (int) $filter;

        return in_array($days, [7, 14, 30, 90], true) ? $days : 14;
    }

    protected function securityDateFilterOptions(): array
    {
        $filters = [];

        if ($this->allowsAllSecurityDateFilter()) {
            $filters['all'] = __('pages.customizations.dashboard.widgets.filters.all_time');
        }

        foreach ([7, 14, 30, 90] as $days) {
            $filters[(string) $days] = __('pages.customizations.dashboard.widgets.filters.last_days', ['days' => $days]);
        }

        return $filters;
    }

    protected function filteredReportsQuery(?Defender $defender = null): Builder
    {
        return $this->applySecurityDateFilter($this->reportsQuery($defender), 'reports.created_at');
    }

    protected function filteredTimelinesQuery(?Defender $defender = null): Builder
    {
        return $this->applySecurityDateFilter($this->timelinesQuery($defender), 'timelines.created_at');
    }

    protected function applySecurityDateFilter(Builder $query, string $column = 'created_at'): Builder
    {
        $days = $this->selectedSecurityDateFilterDays();

        if ($days === null) {
            return $query;
        }

        return $query->where($column, '>=', CarbonImmutable::now()->subDays($days - 1)->startOfDay());
    }

    protected function countSince(Builder $query, CarbonInterface $start): int
    {
        return (int) (clone $query)
            ->where('created_at', '>=', $start)
            ->count();
    }

    protected function countToday(Builder $query): int
    {
        return $this->countSince($query, now()->startOfDay());
    }

    protected function dateCountSeries(Builder $query, int $days = 14): array
    {
        $start = CarbonImmutable::now()->subDays($days - 1)->startOfDay();

        $rows = (clone $query)
            ->where('created_at', '>=', $start)
            ->get([$query->qualifyColumn('created_at')])
            ->countBy(fn (Model $model): string => $model->created_at->toDateString())
            ->map(fn (int $count): int => $count);

        $labels = [];
        $data = [];

        for ($index = 0; $index < $days; $index++) {
            $date = $start->addDays($index);
            $key = $date->toDateString();

            $labels[] = $date->format('M j');
            $data[] = $rows[$key] ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    protected function topReportJsonValues(
        string $column,
        string $path,
        ?Defender $defender = null,
        int $limit = 8,
        ?Builder $query = null,
    ): Collection {
        return (clone ($query ?? $this->reportsQuery($defender)))
            ->get([$column])
            ->map(fn (Report $report): ?string => $this->normalizeSeriesLabel(
                $this->reportJsonValue($report, $column, $path),
            ))
            ->filter(fn (?string $label): bool => filled($label))
            ->countBy()
            ->sortDesc()
            ->take($limit)
            ->map(fn (int $count): int => $count);
    }

    protected function uniqueReportJsonCount(string $column, string $path, ?Defender $defender = null): int
    {
        return (clone $this->reportsQuery($defender))
            ->get([$column])
            ->map(fn (Report $report): ?string => $this->normalizeSeriesLabel(
                $this->reportJsonValue($report, $column, $path),
            ))
            ->filter(fn (?string $label): bool => filled($label))
            ->unique()
            ->count();
    }

    protected function topTriggeredActions(?Defender $defender = null, int $limit = 8): Collection
    {
        return $this->reportsQuery($defender)
            ->with('triggeredBy:id,name')
            ->get(['id', 'triggered_by'])
            ->countBy(fn (Report $report): string => $this->normalizeSeriesLabel(
                $report->triggeredBy?->name,
                __('pages.customizations.dashboard.widgets.empty.unknown'),
            ))
            ->sortDesc()
            ->take($limit)
            ->map(fn (int $count): int => $count);
    }

    protected function topReportingDefenders(int $limit = 8, ?Builder $query = null): Collection
    {
        return (clone ($query ?? $this->reportsQuery()))
            ->with('createdBy:id,name')
            ->get(['id', 'created_by'])
            ->countBy(fn (Report $report): string => $this->normalizeSeriesLabel(
                $report->createdBy?->name,
                __('pages.customizations.dashboard.widgets.empty.unknown'),
            ))
            ->sortDesc()
            ->take($limit)
            ->map(fn (int $count): int => $count);
    }

    protected function groupedDefenderCounts(string $column, string $fallback = 'unknown'): Collection
    {
        return $this->defendersQuery()
            ->get([$column])
            ->countBy(fn (Defender $defender): string => $this->normalizeSeriesLabel(
                $defender->getAttribute($column),
                $fallback,
            ))
            ->map(fn (int $count): int => $count);
    }

    protected function groupedPrincipleValidationCounts(): Collection
    {
        return $this->principlesQuery()
            ->get(['validation_status'])
            ->countBy(fn (Principle $principle): string => $this->normalizeSeriesLabel(
                $principle->validation_status,
                'unknown',
            ))
            ->map(fn (int $count): int => $count);
    }

    protected function groupedTimelineActions(?Defender $defender = null, int $limit = 8, ?Builder $query = null): Collection
    {
        return (clone ($query ?? $this->timelinesQuery($defender)))
            ->get(['action'])
            ->countBy(fn (Timeline $timeline): string => $this->normalizeSeriesLabel(
                $timeline->action,
                'unknown',
            ))
            ->sortDesc()
            ->take($limit)
            ->map(fn (int $count): int => $count);
    }

    protected function policyCoverage(?Defender $defender): array
    {
        if (! $defender) {
            return [
                'principles_total' => 0,
                'principles_applied' => 0,
                'decisions_total' => 0,
                'decisions_implemented' => 0,
            ];
        }

        return [
            'principles_total' => $defender->principles()->count(),
            'principles_applied' => $defender->principles()->wherePivot('is_applied', true)->count(),
            'decisions_total' => $defender->decisions()->count(),
            'decisions_implemented' => $defender->decisions()->wherePivot('is_implemented', true)->count(),
        ];
    }

    protected function reportScatterPoints(?Defender $defender = null, int $limit = 100, ?Builder $query = null): array
    {
        $reports = (clone ($query ?? $this->reportsQuery($defender)))
            ->latest()
            ->limit($limit)
            ->get(['metas', 'created_at'])
            ->sortBy('created_at')
            ->values();

        $start = $reports->first()?->created_at?->copy()->startOfDay();

        if (! $start) {
            return [['x' => 0, 'y' => 0]];
        }

        return $reports
            ->map(fn (Report $report): array => [
                'x' => $start->diffInHours($report->created_at),
                'y' => (int) ($report->metas['status'] ?? 0),
            ])
            ->values()
            ->all();
    }

    protected function bubblePoints(Collection $series): array
    {
        $max = max(1, (int) $series->max());

        return $series
            ->values()
            ->map(fn (int $count, int $index): array => [
                'x' => $index + 1,
                'y' => $count,
                'r' => max(4, min(24, (int) round(($count / $max) * 24))),
            ])
            ->all();
    }

    protected function labelsOrEmpty(Collection $series): array
    {
        $labels = $series->keys()->values()->all();

        return $labels === [] ? [__('pages.customizations.dashboard.widgets.empty.none')] : $labels;
    }

    protected function valuesOrZero(Collection $series): array
    {
        $values = $series->values()->map(fn (mixed $value): int => (int) $value)->all();

        return $values === [] ? [0] : $values;
    }

    protected function formatNumber(int|float $value): string
    {
        if ($value >= 1_000_000) {
            return number_format($value / 1_000_000, 1).'M';
        }

        if ($value >= 1_000) {
            return number_format($value / 1_000, 1).'K';
        }

        return number_format($value);
    }

    protected function chartPalette(): array
    {
        return [
            '#2563eb',
            '#059669',
            '#dc2626',
            '#d97706',
            '#7c3aed',
            '#0891b2',
            '#db2777',
            '#4b5563',
        ];
    }

    protected function reportJsonValue(Report $report, string $column, string $path): mixed
    {
        $path = $this->normalizeJsonPath($path);

        if ($path === null) {
            return $report->getAttribute($column);
        }

        return data_get($report->getAttribute($column), $path);
    }

    protected function normalizeJsonPath(string $path): ?string
    {
        $path = trim($path);

        if ($path === '$' || $path === '') {
            return null;
        }

        if (str_starts_with($path, '$.')) {
            return substr($path, 2);
        }

        if (str_starts_with($path, '$')) {
            return ltrim(substr($path, 1), '.');
        }

        return $path;
    }

    protected function normalizeSeriesLabel(mixed $value, ?string $fallback = null): ?string
    {
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        } elseif ($value instanceof UnitEnum) {
            $value = $value->name;
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } elseif ($value instanceof Stringable || is_scalar($value)) {
            $value = (string) $value;
        }

        if (is_string($value)) {
            $value = trim($value);
        }

        if (blank($value)) {
            return $fallback;
        }

        return (string) $value;
    }
}
