<?php

namespace App\Filament\Widgets\Dashboard;

use App\Enums\Defender\DeploymentStatus;
use App\Enums\Defender\Status;
use App\Enums\Principle\ValidationStatus;
use App\Filament\Widgets\Concerns\InteractsWithSecurityWidgetData;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardOverviewStats extends StatsOverviewWidget
{
    use InteractsWithSecurityWidgetData;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $reportQuery = $this->reportsQuery();
        $timelineQuery = $this->timelinesQuery();
        $reportSeries = $this->dateCountSeries($reportQuery, 7);
        $timelineSeries = $this->dateCountSeries($timelineQuery, 7);
        $defenderQuery = $this->defendersQuery();
        $principleQuery = $this->principlesQuery();

        $defenders = (clone $defenderQuery)->count();
        $normalDefenders = (clone $defenderQuery)->where('status', Status::Normal->value)->count();
        $abnormalDefenders = (clone $defenderQuery)->where('status', Status::Abnormal->value)->count();
        $failedDeployments = (clone $defenderQuery)->where('deployment_status', DeploymentStatus::Failed->value)->count();
        $processingDeployments = (clone $defenderQuery)->where('deployment_status', DeploymentStatus::Processing->value)->count();
        $passedPrinciples = (clone $principleQuery)->where('validation_status', ValidationStatus::Passed->value)->count();
        $principles = (clone $principleQuery)->count();
        $topIp = $this->topReportJsonValues('metas', '$.ip', null, 1)->keys()->first()
            ?? __('pages.customizations.dashboard.widgets.empty.none');

        return [
            Stat::make(__('pages.customizations.dashboard.widgets.stats.defenders'), $this->formatNumber($defenders))
                ->description(__('pages.customizations.dashboard.widgets.descriptions.normal_abnormal', [
                    'normal' => $this->formatNumber($normalDefenders),
                    'abnormal' => $this->formatNumber($abnormalDefenders),
                ]))
                ->descriptionIcon($abnormalDefenders > 0 ? Heroicon::OutlinedExclamationCircle : Heroicon::OutlinedCheckCircle)
                ->color($abnormalDefenders > 0 ? 'danger' : 'info'),

            Stat::make(__('pages.customizations.dashboard.widgets.stats.reports'), $this->formatNumber($reportQuery->count()))
                ->description(__('pages.customizations.dashboard.widgets.descriptions.today_24h', [
                    'today' => $this->formatNumber($this->countToday($this->reportsQuery())),
                    'last24h' => $this->formatNumber($this->countSince($this->reportsQuery(), now()->subDay())),
                ]))
                ->descriptionIcon(Heroicon::OutlinedDocumentChartBar)
                ->chart($reportSeries['data'])
                ->color('primary'),

            Stat::make(__('pages.customizations.dashboard.widgets.stats.unique_ips'), $this->formatNumber($this->uniqueReportJsonCount('metas', '$.ip')))
                ->description(__('pages.customizations.dashboard.widgets.descriptions.top_ip', ['ip' => $topIp]))
                ->descriptionIcon(Heroicon::OutlinedGlobeAlt)
                ->color('teal'),

            Stat::make(__('pages.customizations.dashboard.widgets.stats.timelines'), $this->formatNumber($timelineQuery->count()))
                ->description(__('pages.customizations.dashboard.widgets.descriptions.today', [
                    'count' => $this->formatNumber($this->countToday($this->timelinesQuery())),
                ]))
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->chart($timelineSeries['data'])
                ->color('slate'),

            Stat::make(__('pages.customizations.dashboard.widgets.stats.failed_deployments'), $this->formatNumber($failedDeployments))
                ->description(__('pages.customizations.dashboard.widgets.descriptions.processing', [
                    'processing' => $this->formatNumber($processingDeployments),
                ]))
                ->descriptionIcon($failedDeployments > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedServerStack)
                ->color($failedDeployments > 0 ? 'danger' : 'success'),

            Stat::make(__('pages.customizations.dashboard.widgets.stats.validated_principles'), $this->formatNumber($passedPrinciples))
                ->description(__('pages.customizations.dashboard.widgets.descriptions.passed_total', [
                    'passed' => $this->formatNumber($passedPrinciples),
                    'total' => $this->formatNumber($principles),
                ]))
                ->descriptionIcon(Heroicon::OutlinedRectangleStack)
                ->color('emerald'),
        ];
    }
}
