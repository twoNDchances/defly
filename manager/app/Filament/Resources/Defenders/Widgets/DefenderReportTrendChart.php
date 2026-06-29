<?php

namespace App\Filament\Resources\Defenders\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithSecurityWidgetData;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class DefenderReportTrendChart extends ChartWidget
{
    use InteractsWithSecurityWidgetData;

    public ?Model $record = null;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = '10s';

    protected bool $hasSecurityDateFilter = true;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = null;

    protected function getData(): array
    {
        $series = $this->dateCountSeries(
            $this->reportsQuery($this->currentDefender()),
            $this->selectedSecurityDateFilterDays() ?? 14,
        );

        return [
            'datasets' => [
                [
                    'label' => __('pages.customizations.dashboard.widgets.datasets.reports'),
                    'data' => $series['data'],
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.18)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    public function getHeading(): ?string
    {
        return __('pages.customizations.dashboard.widgets.charts.reports_trend');
    }

    protected function getType(): string
    {
        return 'line';
    }
}
