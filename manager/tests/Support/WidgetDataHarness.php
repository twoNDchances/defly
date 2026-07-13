<?php

namespace Tests\Support;

use App\Filament\Widgets\Concerns\InteractsWithSecurityWidgetData;
use App\Models\Defender;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class WidgetDataHarness
{
    use InteractsWithSecurityWidgetData;

    public ?Model $record = null;

    public ?string $filter = null;

    protected bool $hasSecurityDateFilter = true;

    protected bool $allowsAllSecurityDateFilter = true;

    protected string $defaultSecurityDateFilter = 'all';

    public function currentDefenderPublic(): ?Defender
    {
        return $this->currentDefender();
    }

    public function filtersPublic(): ?array
    {
        return $this->getFilters();
    }

    public function selectedSecurityDateFilterDaysPublic(): ?int
    {
        return $this->selectedSecurityDateFilterDays();
    }

    public function reportsQueryPublic(?Defender $defender = null): Builder
    {
        return $this->reportsQuery($defender);
    }

    public function filteredReportsQueryPublic(?Defender $defender = null): Builder
    {
        return $this->filteredReportsQuery($defender);
    }

    public function timelinesQueryPublic(?Defender $defender = null): Builder
    {
        return $this->timelinesQuery($defender);
    }

    public function filteredTimelinesQueryPublic(?Defender $defender = null): Builder
    {
        return $this->filteredTimelinesQuery($defender);
    }

    public function countTodayPublic(Builder $query): int
    {
        return $this->countToday($query);
    }

    public function dateCountSeriesPublic(Builder $query, int $days): array
    {
        return $this->dateCountSeries($query, $days);
    }

    public function topReportJsonValuesPublic(string $column, string $path, ?Defender $defender = null): Collection
    {
        return $this->topReportJsonValues($column, $path, $defender);
    }

    public function uniqueReportJsonCountPublic(string $column, string $path, ?Defender $defender = null): int
    {
        return $this->uniqueReportJsonCount($column, $path, $defender);
    }

    public function topTriggeredActionsPublic(?Defender $defender = null): Collection
    {
        return $this->topTriggeredActions($defender);
    }

    public function topReportingDefendersPublic(): Collection
    {
        return $this->topReportingDefenders();
    }

    public function groupedDefenderCountsPublic(string $column): Collection
    {
        return $this->groupedDefenderCounts($column);
    }

    public function groupedPrincipleValidationCountsPublic(): Collection
    {
        return $this->groupedPrincipleValidationCounts();
    }

    public function groupedTimelineActionsPublic(?Defender $defender = null): Collection
    {
        return $this->groupedTimelineActions($defender);
    }

    public function policyCoveragePublic(?Defender $defender): array
    {
        return $this->policyCoverage($defender);
    }

    public function reportScatterPointsPublic(?Defender $defender = null): array
    {
        return $this->reportScatterPoints($defender);
    }

    public function bubblePointsPublic(Collection $series): array
    {
        return $this->bubblePoints($series);
    }

    public function labelsOrEmptyPublic(Collection $series): array
    {
        return $this->labelsOrEmpty($series);
    }

    public function valuesOrZeroPublic(Collection $series): array
    {
        return $this->valuesOrZero($series);
    }

    public function formatNumberPublic(int|float $value): string
    {
        return $this->formatNumber($value);
    }

    public function chartPalettePublic(): array
    {
        return $this->chartPalette();
    }
}
