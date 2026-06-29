<?php

namespace App\Filament\Clusters\Infrastructure\Resources\Defenders\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithSecurityWidgetData;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;

class DefenderHttpStatusChart extends ChartWidget
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
        $series = $this->topReportJsonValues('metas', '$.status', $defender, 8, $this->filteredReportsQuery($defender));
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
        return 'doughnut';
    }
}
