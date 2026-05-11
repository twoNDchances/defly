<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Concerns\InteractsWithSecurityWidgetData;
use Filament\Widgets\ChartWidget;

class ReportsByDefenderChart extends ChartWidget
{
    use InteractsWithSecurityWidgetData;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = '10s';

    protected function getData(): array
    {
        $series = $this->topReportingDefenders();

        return [
            'datasets' => [
                [
                    'label' => __('pages.customizations.dashboard.widgets.datasets.reports'),
                    'data' => $this->valuesOrZero($series),
                    'backgroundColor' => $this->chartPalette(),
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $this->labelsOrEmpty($series),
        ];
    }

    public function getHeading(): ?string
    {
        return __('pages.customizations.dashboard.widgets.charts.reports_by_defender');
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
