<?php

namespace App\Filament\Clusters\Infrastructure\Resources\Defenders\Widgets;

use App\Enums\Defender\Status;
use App\Filament\Widgets\Concerns\InteractsWithSecurityWidgetData;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;

class DefenderOverviewStats extends StatsOverviewWidget
{
    use InteractsWithSecurityWidgetData;

    public ?Model $record = null;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $defender = $this->currentDefender();
        $reportQuery = $this->reportsQuery($defender);
        $reportSeries = $this->dateCountSeries($reportQuery, 7);
        $coverage = $this->policyCoverage($defender);
        $details = $defender?->details ?? [];
        $status = $defender?->status?->value ?? $defender?->status ?? 'unknown';
        $lastReport = (clone $this->reportsQuery($defender))->latest()->first()?->created_at;
        $topIp = $this->topReportJsonValues('metas', '$.ip', $defender, 1)->keys()->first()
            ?? __('pages.customizations.dashboard.widgets.empty.none');

        return [
            Stat::make(__('pages.customizations.dashboard.widgets.stats.health'), Lang::has("pages.customizations.dashboard.widgets.labels.{$status}")
                ? __("pages.customizations.dashboard.widgets.labels.{$status}")
                : str($status)->headline()->toString())
                ->description(__('pages.customizations.dashboard.widgets.descriptions.status_reason', [
                    'status' => collect($details['reasons'] ?? [])->first() ?? __('pages.customizations.dashboard.widgets.empty.none'),
                ]))
                ->descriptionIcon($status === Status::Abnormal->value ? Heroicon::OutlinedExclamationCircle : Heroicon::OutlinedCheckCircle)
                ->color($status === Status::Abnormal->value ? 'danger' : 'success'),

            Stat::make(__('pages.customizations.dashboard.widgets.stats.reports'), $this->formatNumber($reportQuery->count()))
                ->description($lastReport
                    ? __('pages.customizations.dashboard.widgets.descriptions.last_report', ['time' => $lastReport->diffForHumans()])
                    : __('pages.customizations.dashboard.widgets.descriptions.last_report', ['time' => __('pages.customizations.dashboard.widgets.empty.none')]))
                ->descriptionIcon(Heroicon::OutlinedDocumentChartBar)
                ->chart($reportSeries['data'])
                ->color('primary'),

            Stat::make(__('pages.customizations.dashboard.widgets.stats.unique_ips'), $this->formatNumber($this->uniqueReportJsonCount('metas', '$.ip', $defender)))
                ->description(__('pages.customizations.dashboard.widgets.descriptions.top_ip', ['ip' => $topIp]))
                ->descriptionIcon(Heroicon::OutlinedGlobeAlt)
                ->color('teal'),

            Stat::make(__('pages.customizations.dashboard.widgets.stats.principles'), $this->formatNumber($coverage['principles_applied']))
                ->description(__('pages.customizations.dashboard.widgets.descriptions.applied_total', [
                    'applied' => $this->formatNumber($coverage['principles_applied']),
                    'total' => $this->formatNumber($coverage['principles_total']),
                ]))
                ->descriptionIcon(Heroicon::OutlinedRectangleStack)
                ->color('emerald'),

            Stat::make(__('pages.customizations.dashboard.widgets.stats.decisions'), $this->formatNumber($coverage['decisions_implemented']))
                ->description(__('pages.customizations.dashboard.widgets.descriptions.implemented_total', [
                    'implemented' => $this->formatNumber($coverage['decisions_implemented']),
                    'total' => $this->formatNumber($coverage['decisions_total']),
                ]))
                ->descriptionIcon(Heroicon::OutlinedBolt)
                ->color('orange'),

            Stat::make(__('pages.customizations.dashboard.widgets.stats.memory'), $this->formatNumber((int) ($details['process_memory_sys_mib'] ?? 0)).' MiB')
                ->description(__('pages.customizations.dashboard.widgets.descriptions.goroutines', [
                    'count' => $this->formatNumber((int) ($details['goroutines'] ?? 0)),
                ]))
                ->descriptionIcon(Heroicon::OutlinedServer)
                ->color('slate'),
        ];
    }
}
