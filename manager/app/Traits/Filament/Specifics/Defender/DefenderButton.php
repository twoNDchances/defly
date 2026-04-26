<?php

namespace App\Traits\Filament\Specifics\Defender;

use App\Enums\Defender\DeploymentStatus;
use App\Traits\Filament\Generals\Components\Button;
use Filament\Support\Icons\Heroicon;

trait DefenderButton
{
    use Button;

    public static function deployDefenderButton()
    {
        return self::button(
            'deploy_button',
            __('tables.defender.buttons.deploy_button'),
            Heroicon::OutlinedRocketLaunch,
            function ($record) {
                $record->deployment_status = DeploymentStatus::Pending;
                $record->save();
            }
        )
            ->authorize('deploy')
            ->color('teal');
    }

    public static function deployDefenderBulkButton()
    {
        return self::bulkButton(
            'deploy_bulk_button',
            __('tables.defender.buttons.deploy_bulk_button'),
            Heroicon::OutlinedRocketLaunch,
            function ($records) {
                foreach ($records as $record) {
                    if (in_array($record->deployment_status, [
                        DeploymentStatus::Pending,
                        DeploymentStatus::Deploying,
                    ], true)) {
                        continue;
                    }
                    $record->deployment_status = DeploymentStatus::Pending;
                    $record->save();
                }
            }
        )
            ->authorize('deployAny')
            ->color('teal')
            ->requiresConfirmation()
            ->chunkSelectedRecords(100)
            ->deselectRecordsAfterCompletion();
    }

    public static function deleteDoneBulkButton()
    {
        return self::deleteBulkButton()
            ->action(function ($records) {
                foreach ($records as $record) {
                    if (in_array($record->deployment_status, [
                        DeploymentStatus::Pending,
                        DeploymentStatus::Deploying,
                    ], true)) {
                        continue;
                    }
                    $record->delete();
                }
            });
    }
}
