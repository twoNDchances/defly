<?php

namespace App\Filament\Clusters\Infrastructure\Resources\Defenders\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithSecurityWidgetData;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class DefenderTrafficMethodChart extends ChartWidget
{
    use InteractsWithSecurityWidgetData;

    public ?Model $record = null;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = '10s';

    protected bool $hasSecurityDateFilter = true;

    protected bool $allowsAllSecurityDateFilter = true;

    protected string $defaultSecurityDateFilter = 'all';

    protected ?string $heading = null;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $defender = $this->currentDefender();
        $series = $this->topReportJsonValues('metas', '$.method', $defender, 8, $this->filteredReportsQuery($defender));
        $labels = $series->keys()->map(fn (string $method): string => strtoupper($method))->all();

        return [
            'datasets' => [
                [
                    'label' => __('pages.customizations.dashboard.widgets.datasets.methods'),
                    'data' => $this->valuesOrZero($series),
                    'backgroundColor' => $this->chartPalette(),
                ],
            ],
            'labels' => $labels === [] ? [__('pages.customizations.dashboard.widgets.empty.none')] : $labels,
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
