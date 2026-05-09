<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Concerns\InteractsWithSecurityWidgetData;
use Filament\Widgets\ChartWidget;

class TopRulesRadarChart extends ChartWidget
{
    use InteractsWithSecurityWidgetData;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $series = $this->topReportJsonValues('rule_details', '$.rule.name', null, 8);

        return [
            'datasets' => [
                [
                    'label' => __('pages.customizations.dashboard.widgets.datasets.rules'),
                    'data' => $this->valuesOrZero($series),
                    'backgroundColor' => 'rgba(124, 58, 237, 0.22)',
                    'borderColor' => '#7c3aed',
                    'pointBackgroundColor' => '#7c3aed',
                ],
            ],
            'labels' => $this->labelsOrEmpty($series),
        ];
    }

    public function getHeading(): ?string
    {
        return __('pages.customizations.dashboard.widgets.charts.top_rules');
    }

    protected function getType(): string
    {
        return 'radar';
    }
}
