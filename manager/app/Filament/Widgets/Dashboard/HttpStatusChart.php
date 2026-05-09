<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Concerns\InteractsWithSecurityWidgetData;
use Filament\Widgets\ChartWidget;
use Symfony\Component\HttpFoundation\Response;

class HttpStatusChart extends ChartWidget
{
    use InteractsWithSecurityWidgetData;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $series = $this->topReportJsonValues('metas', '$.status', null, 8);
        $labels = $series->keys()
            ->map(fn (string $status): string => filled(Response::$statusTexts[(int) $status] ?? null)
                ? "[{$status}] ".Response::$statusTexts[(int) $status]
                : $status)
            ->all();

        return [
            'datasets' => [
                [
                    'label' => __('pages.customizations.dashboard.widgets.datasets.status_codes'),
                    'data' => $this->valuesOrZero($series),
                    'backgroundColor' => $this->chartPalette(),
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $labels === [] ? [__('pages.customizations.dashboard.widgets.empty.none')] : $labels,
        ];
    }

    public function getHeading(): ?string
    {
        return __('pages.customizations.dashboard.widgets.charts.http_status');
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
