<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Dashboard\DashboardOverviewStats;
use App\Filament\Widgets\Dashboard\DefenderHealthChart;
use App\Filament\Widgets\Dashboard\DeploymentStatusChart;
use App\Filament\Widgets\Dashboard\HttpStatusChart;
use App\Filament\Widgets\Dashboard\IpBubbleChart;
use App\Filament\Widgets\Dashboard\ReportsByDefenderChart;
use App\Filament\Widgets\Dashboard\ReportsScatterChart;
use App\Filament\Widgets\Dashboard\ReportsTrendChart;
use App\Filament\Widgets\Dashboard\TimelineActionsChart;
use App\Filament\Widgets\Dashboard\TimelineActivityChart;
use App\Filament\Widgets\Dashboard\TopRulesRadarChart;
use App\Filament\Widgets\Dashboard\TrafficMethodChart;
use App\Models\Defender;
use App\Models\Principle;
use App\Models\Report;
use App\Models\Timeline;
use App\Services\Security;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends Page
{
    protected string $view = 'filament.pages.dashboard';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    public static function getNavigationLabel(): string
    {
        return __('pages.customizations.dashboard.title');
    }

    public function getTitle(): string|Htmlable
    {
        return __('pages.customizations.dashboard.title');
    }

    public static function canAccess(): bool
    {
        return Security::can(Defender::class, 'viewAny')
            || Security::can(Principle::class, 'viewAny')
            || Security::can(Report::class, 'viewAny')
            || Security::can(Timeline::class, 'viewAny');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DashboardOverviewStats::class,
            ReportsTrendChart::class,
            ReportsByDefenderChart::class,
            DefenderHealthChart::class,
            DeploymentStatusChart::class,
            TrafficMethodChart::class,
            TimelineActionsChart::class,
            HttpStatusChart::class,
            TopRulesRadarChart::class,
            IpBubbleChart::class,
            ReportsScatterChart::class,
            TimelineActivityChart::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }
}
