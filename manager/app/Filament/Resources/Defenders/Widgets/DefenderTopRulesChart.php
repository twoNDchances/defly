<?php

namespace App\Filament\Resources\Defenders\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithSecurityWidgetData;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class DefenderTopRulesChart extends ChartWidget
{
    use InteractsWithSecurityWidgetData;

    public ?Model $record = null;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = '30s';

    protected ?string $heading = null;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $series = $this->topReportJsonValues('rule_details', '$.rule.name', $this->currentDefender(), 8);

        return [
            'datasets' => [
                [
                    'label' => __('pages.customizations.dashboard.widgets.datasets.rules'),
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
        return __('pages.customizations.dashboard.widgets.charts.top_rules');
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
