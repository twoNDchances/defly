<?php

namespace App\Filament\Clusters\Authentication;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class AuthenticationCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('navigations.groups.security');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigations.clusters.authentication');
    }

    public static function getClusterBreadcrumb(): string
    {
        return __('navigations.clusters.authentication');
    }
}
