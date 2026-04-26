<?php

namespace App\Filament\Clusters\Initialization;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class InitializationCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCursorArrowRipple;

    protected static ?int $navigationSort = 2;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('navigations.groups.management');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigations.clusters.initialization');
    }

    public static function getClusterBreadcrumb(): string
    {
        return __('navigations.clusters.initialization');
    }
}
