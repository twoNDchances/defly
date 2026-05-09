<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Concerns\InteractsWithSecurityWidgetData;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Lang;

class DeploymentStatusChart extends ChartWidget
{
    use InteractsWithSecurityWidgetData;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $series = $this->groupedDefenderCounts('deployment_status');
        $labels = $series->keys()
            ->map(fn (string $status): string => Lang::has("pages.customizations.dashboard.widgets.labels.{$status}")
                ? __("pages.customizations.dashboard.widgets.labels.{$status}")
                : str($status)->headline()->toString())
            ->all();

        return [
            'datasets' => [
                [
                    'label' => __('pages.customizations.dashboard.widgets.datasets.statuses'),
                    'data' => $this->valuesOrZero($series),
                    'backgroundColor' => ['#6b7280', '#0891b2', '#dc2626', '#059669', '#d97706'],
                ],
            ],
            'labels' => $labels === [] ? [__('pages.customizations.dashboard.widgets.empty.none')] : $labels,
        ];
    }

    public function getHeading(): ?string
    {
        return __('pages.customizations.dashboard.widgets.charts.deployment_status');
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
