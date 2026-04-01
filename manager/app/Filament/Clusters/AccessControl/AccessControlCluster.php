<?php

namespace App\Filament\Clusters\AccessControl;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class AccessControlCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLockClosed;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('navigations.groups.security');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigations.clusters.access_control');
    }

    public static function getClusterBreadcrumb(): string
    {
        return __('navigations.clusters.access_control');
    }
}
