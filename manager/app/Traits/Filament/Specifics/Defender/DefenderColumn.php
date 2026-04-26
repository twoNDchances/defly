<?php

namespace App\Traits\Filament\Specifics\Defender;

use App\Traits\Filament\Generals\Components\Column;

trait DefenderColumn
{
    use Column, DefenderButton, DefenderData;

    public static function getName()
    {
        return self::textColumn('name', __('models.defender.fields.name'));
    }

    public static function getStatus()
    {
        return self::booleanColumn('status', __('models.defender.fields.status'));
    }

    public static function getDeploymentStatus()
    {
        return self::textColumn('deployment_status', __('models.defender.fields.deployment_status'))
            ->formatStateUsing(fn ($state) => self::deploymentStatusOptionsAndColors()['options'][$state->value])
            ->color(fn ($state) => self::deploymentStatusOptionsAndColors()['colors'][$state->value])
            ->badge();
    }

    public static function getPolicies()
    {
        return self::relationshipColumn('policies.name', __('tables.defender.policies'));
    }

    public static function getDecisions()
    {
        return self::relationshipColumn('decisions.name', __('tables.defender.decisions'));
    }
}
