<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Concerns\InteractsWithSecurityWidgetData;
use Filament\Widgets\ChartWidget;

class TimelineActivityChart extends ChartWidget
{
    use InteractsWithSecurityWidgetData;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = '10s';

    protected bool $hasSecurityDateFilter = true;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $series = $this->dateCountSeries($this->timelinesQuery(), $this->selectedSecurityDateFilterDays() ?? 14);

        return [
            'datasets' => [
                [
                    'label' => __('pages.customizations.dashboard.widgets.datasets.timelines'),
                    'data' => $series['data'],
                    'borderColor' => '#4b5563',
                    'backgroundColor' => 'rgba(75, 85, 99, 0.18)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    public function getHeading(): ?string
    {
        return __('pages.customizations.dashboard.widgets.charts.timeline_activity');
    }

    protected function getType(): string
    {
        return 'line';
    }
}
