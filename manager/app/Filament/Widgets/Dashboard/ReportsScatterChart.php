<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Concerns\InteractsWithSecurityWidgetData;
use Filament\Widgets\ChartWidget;

class ReportsScatterChart extends ChartWidget
{
    use InteractsWithSecurityWidgetData;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = '10s';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => __('pages.customizations.dashboard.widgets.datasets.status_codes'),
                    'data' => $this->reportScatterPoints(),
                    'backgroundColor' => 'rgba(220, 38, 38, 0.35)',
                    'borderColor' => '#dc2626',
                ],
            ],
        ];
    }

    public function getHeading(): ?string
    {
        return __('pages.customizations.dashboard.widgets.charts.reports_scatter');
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => __('pages.customizations.dashboard.widgets.labels.hours_since_first_report'),
                    ],
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => __('pages.customizations.dashboard.widgets.datasets.status_codes'),
                    ],
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'scatter';
    }
}
