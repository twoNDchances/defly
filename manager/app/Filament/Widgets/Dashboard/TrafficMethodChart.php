<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Concerns\InteractsWithSecurityWidgetData;
use Filament\Widgets\ChartWidget;

class TrafficMethodChart extends ChartWidget
{
    use InteractsWithSecurityWidgetData;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $series = $this->topReportJsonValues('metas', '$.method', null, 8);
        $labels = $series->keys()->map(fn (string $method): string => strtoupper($method));

        return [
            'datasets' => [
                [
                    'label' => __('pages.customizations.dashboard.widgets.datasets.methods'),
                    'data' => $this->valuesOrZero($series),
                    'backgroundColor' => $this->chartPalette(),
                ],
            ],
            'labels' => $labels->all() ?: [__('pages.customizations.dashboard.widgets.empty.none')],
        ];
    }

    public function getHeading(): ?string
    {
        return __('pages.customizations.dashboard.widgets.charts.traffic_method');
    }

    protected function getType(): string
    {
        return 'polarArea';
    }
}
