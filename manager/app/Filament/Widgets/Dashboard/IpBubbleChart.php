<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Concerns\InteractsWithSecurityWidgetData;
use Filament\Widgets\ChartWidget;

class IpBubbleChart extends ChartWidget
{
    use InteractsWithSecurityWidgetData;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $series = $this->topReportJsonValues('metas', '$.ip', null, 8);

        return [
            'datasets' => [
                [
                    'label' => __('pages.customizations.dashboard.widgets.datasets.ips'),
                    'data' => $this->bubblePoints($series),
                    'backgroundColor' => 'rgba(8, 145, 178, 0.35)',
                    'borderColor' => '#0891b2',
                ],
            ],
            'labels' => $this->labelsOrEmpty($series),
        ];
    }

    public function getHeading(): ?string
    {
        return __('pages.customizations.dashboard.widgets.charts.ip_bubbles');
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bubble';
    }
}
