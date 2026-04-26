<?php

namespace App\Filament\Clusters\Context;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ContextCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedViewfinderCircle;

    protected static ?int $navigationSort = 3;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('navigations.groups.management');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigations.clusters.context');
    }

    public static function getClusterBreadcrumb(): string
    {
        return __('navigations.clusters.context');
    }
}
