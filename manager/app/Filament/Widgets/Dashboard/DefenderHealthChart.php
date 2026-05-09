<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Concerns\InteractsWithSecurityWidgetData;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Lang;

class DefenderHealthChart extends ChartWidget
{
    use InteractsWithSecurityWidgetData;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $series = $this->groupedDefenderCounts('status');
        $labels = $series->keys()
            ->map(fn (string $status): string => Lang::has("pages.customizations.dashboard.widgets.labels.{$status}")
                ? __("pages.customizations.dashboard.widgets.labels.{$status}")
                : str($status)->headline()->toString())
            ->all();

        return [
            'datasets' => [
                [
                    'label' => __('pages.customizations.dashboard.widgets.datasets.defenders'),
                    'data' => $this->valuesOrZero($series),
                    'backgroundColor' => ['#059669', '#dc2626', '#6b7280'],
                ],
            ],
            'labels' => $labels === [] ? [__('pages.customizations.dashboard.widgets.empty.none')] : $labels,
        ];
    }

    public function getHeading(): ?string
    {
        return __('pages.customizations.dashboard.widgets.charts.defender_health');
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
