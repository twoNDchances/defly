<?php

namespace App\Filament\Clusters\Infrastructure\Resources\Defenders\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithSecurityWidgetData;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class DefenderPolicyCoverageChart extends ChartWidget
{
    use InteractsWithSecurityWidgetData;

    public ?Model $record = null;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = '10s';

    protected ?string $heading = null;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $coverage = $this->policyCoverage($this->currentDefender());

        return [
            'datasets' => [
                [
                    'label' => __('pages.customizations.dashboard.widgets.datasets.applied'),
                    'data' => [
                        $coverage['principles_applied'],
                        $coverage['decisions_implemented'],
                    ],
                    'backgroundColor' => '#059669',
                    'borderRadius' => 6,
                ],
                [
                    'label' => __('pages.customizations.dashboard.widgets.datasets.not_applied'),
                    'data' => [
                        max(0, $coverage['principles_total'] - $coverage['principles_applied']),
                        max(0, $coverage['decisions_total'] - $coverage['decisions_implemented']),
                    ],
                    'backgroundColor' => '#d97706',
                    'borderRadius' => 6,
                ],
            ],
            'labels' => [
                __('pages.customizations.dashboard.widgets.datasets.principles'),
                __('pages.customizations.dashboard.widgets.datasets.decisions'),
            ],
        ];
    }

    public function getHeading(): ?string
    {
        return __('pages.customizations.dashboard.widgets.charts.policy_coverage');
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
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
        return 'bar';
    }
}
