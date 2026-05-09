<?php

namespace App\Filament\Widgets\Concerns;

use App\Models\Defender;
use App\Models\Principle;
use App\Models\Report;
use App\Models\Timeline;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait InteractsWithSecurityWidgetData
{
    protected function currentDefender(): ?Defender
    {
        $record = $this->record ?? null;

        return $record instanceof Defender ? $record : null;
    }

    protected function reportsQuery(?Defender $defender = null): Builder
    {
        return Report::query()
            ->when($defender, fn (Builder $query): Builder => $query->where('created_by', $defender->getKey()));
    }

    protected function timelinesQuery(?Defender $defender = null): Builder
    {
        return Timeline::query()
            ->when($defender, fn (Builder $query): Builder => $query
                ->where('resource_type', Defender::class)
                ->where('resource_id', $defender->getKey()));
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
            ->selectRaw('DATE(created_at) as date_key, COUNT(*) as aggregate')
            ->groupByRaw('DATE(created_at)')
            ->pluck('aggregate', 'date_key')
            ->map(fn (mixed $count): int => (int) $count);

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
    ): Collection {
        $expression = $this->jsonValueExpression($column, $path);

        return (clone $this->reportsQuery($defender))
            ->selectRaw("{$expression} as label, COUNT(*) as aggregate")
            ->whereRaw("{$expression} IS NOT NULL")
            ->whereRaw("{$expression} <> ''")
            ->groupByRaw($expression)
            ->orderByDesc('aggregate')
            ->limit($limit)
            ->get()
            ->mapWithKeys(fn (Report $row): array => [(string) $row->label => (int) $row->aggregate]);
    }

    protected function uniqueReportJsonCount(string $column, string $path, ?Defender $defender = null): int
    {
        $expression = $this->jsonValueExpression($column, $path);

        return (int) (clone $this->reportsQuery($defender))
            ->whereRaw("{$expression} IS NOT NULL")
            ->whereRaw("{$expression} <> ''")
            ->selectRaw("COUNT(DISTINCT {$expression}) as aggregate")
            ->value('aggregate');
    }

    protected function topTriggeredActions(?Defender $defender = null, int $limit = 8): Collection
    {
        return Report::query()
            ->leftJoin('actions', 'actions.id', '=', 'reports.triggered_by')
            ->when($defender, fn (Builder $query): Builder => $query->where('reports.created_by', $defender->getKey()))
            ->selectRaw('actions.name as label, COUNT(*) as aggregate')
            ->groupBy('actions.name')
            ->orderByDesc('aggregate')
            ->limit($limit)
            ->get()
            ->mapWithKeys(fn (Report $row): array => [
                filled($row->label) ? (string) $row->label : __('pages.customizations.dashboard.widgets.empty.unknown') => (int) $row->aggregate,
            ]);
    }

    protected function topReportingDefenders(int $limit = 8): Collection
    {
        return Report::query()
            ->leftJoin('defenders', 'defenders.id', '=', 'reports.created_by')
            ->selectRaw('defenders.name as label, COUNT(*) as aggregate')
            ->groupBy('defenders.id', 'defenders.name')
            ->orderByDesc('aggregate')
            ->limit($limit)
            ->get()
            ->mapWithKeys(fn (Report $row): array => [
                filled($row->label) ? (string) $row->label : __('pages.customizations.dashboard.widgets.empty.unknown') => (int) $row->aggregate,
            ]);
    }

    protected function groupedDefenderCounts(string $column, string $fallback = 'unknown'): Collection
    {
        return Defender::query()
            ->selectRaw("COALESCE({$column}, ?) as label, COUNT(*) as aggregate", [$fallback])
            ->groupBy($column)
            ->pluck('aggregate', 'label')
            ->map(fn (mixed $count): int => (int) $count);
    }

    protected function groupedPrincipleValidationCounts(): Collection
    {
        return Principle::query()
            ->selectRaw('COALESCE(validation_status, ?) as label, COUNT(*) as aggregate', ['unknown'])
            ->groupBy('validation_status')
            ->pluck('aggregate', 'label')
            ->map(fn (mixed $count): int => (int) $count);
    }

    protected function groupedTimelineActions(?Defender $defender = null, int $limit = 8): Collection
    {
        return (clone $this->timelinesQuery($defender))
            ->selectRaw("COALESCE(action, 'unknown') as label, COUNT(*) as aggregate")
            ->groupByRaw("COALESCE(action, 'unknown')")
            ->orderByDesc('aggregate')
            ->limit($limit)
            ->pluck('aggregate', 'label')
            ->map(fn (mixed $count): int => (int) $count);
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

    protected function reportScatterPoints(?Defender $defender = null, int $limit = 100): array
    {
        $reports = (clone $this->reportsQuery($defender))
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

    protected function jsonValueExpression(string $column, string $path): string
    {
        return "JSON_UNQUOTE(JSON_EXTRACT({$column}, '{$path}'))";
    }
}
