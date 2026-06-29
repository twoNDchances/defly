<?php

namespace App\Filament\Clusters\Infrastructure\Resources\Defenders\Pages;

use App\Filament\Clusters\Infrastructure\Resources\Defenders\DefenderResource;
use App\Filament\Clusters\Infrastructure\Resources\Defenders\Widgets\DefenderHttpStatusChart;
use App\Filament\Clusters\Infrastructure\Resources\Defenders\Widgets\DefenderOverviewStats;
use App\Filament\Clusters\Infrastructure\Resources\Defenders\Widgets\DefenderPolicyCoverageChart;
use App\Filament\Clusters\Infrastructure\Resources\Defenders\Widgets\DefenderReportTrendChart;
use App\Filament\Clusters\Infrastructure\Resources\Defenders\Widgets\DefenderTopRulesChart;
use App\Filament\Clusters\Infrastructure\Resources\Defenders\Widgets\DefenderTopSourcesChart;
use App\Filament\Clusters\Infrastructure\Resources\Defenders\Widgets\DefenderTrafficMethodChart;
use App\Traits\Filament\Generals\Pages\EditPage;
use App\Traits\Filament\Specifics\Defender\DefenderData;
use Filament\Resources\Pages\EditRecord;

class EditDefender extends EditRecord
{
    use DefenderData, EditPage;

    protected static string $resource = DefenderResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return self::loadForm($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return self::saveForm($data);
    }

    protected function getFooterWidgets(): array
    {
        return [
            DefenderOverviewStats::class,
            DefenderPolicyCoverageChart::class,
            DefenderReportTrendChart::class,
            DefenderTopRulesChart::class,
            DefenderTrafficMethodChart::class,
            DefenderHttpStatusChart::class,
            DefenderTopSourcesChart::class,
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 2;
    }
}
