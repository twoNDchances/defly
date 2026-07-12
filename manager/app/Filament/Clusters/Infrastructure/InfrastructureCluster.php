<?php

namespace App\Filament\Clusters\Infrastructure;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class InfrastructureCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::ServerStack;

    protected static ?int $navigationSort = 1;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('navigations.groups.management');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigations.clusters.infrastructure');
    }

    public static function getClusterBreadcrumb(): string
    {
        return __('navigations.clusters.infrastructure');
    }
}
