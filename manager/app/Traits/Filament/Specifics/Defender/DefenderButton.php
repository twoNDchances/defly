<?php

namespace App\Traits\Filament\Specifics\Defender;

use App\Enums\Defender\DeploymentStatus;
use App\Jobs\DefenderDeployment;
use App\Services\Orchestrator;
use App\Traits\Filament\Generals\Components\Button;
use Filament\Support\Icons\Heroicon;
use Throwable;

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
                if (in_array($record->deployment_status, [DeploymentStatus::Pending, DeploymentStatus::Processing], true)) {
                    return;
                }

                $record->deployment_status = DeploymentStatus::Pending;
                $record->save();
                DefenderDeployment::dispatch($record->id, DefenderDeployment::ACTION_DEPLOY);
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
                        DeploymentStatus::Processing,
                    ], true)) {
                        continue;
                    }
                    $record->deployment_status = DeploymentStatus::Pending;
                    $record->save();
                    DefenderDeployment::dispatch($record->id, DefenderDeployment::ACTION_DEPLOY);
                }
            }
        )
            ->authorize('deployAny')
            ->color('teal')
            ->requiresConfirmation()
            ->chunkSelectedRecords(100)
            ->deselectRecordsAfterCompletion();
    }

    public static function cancelDefenderButton()
    {
        return self::button(
            'cancel_button',
            __('tables.defender.buttons.cancel_button'),
            Heroicon::OutlinedArchiveBoxXMark,
            function ($record) {
                if ($record->deployment_status !== DeploymentStatus::Successful) {
                    return;
                }

                $record->forceFill([
                    'deployment_status' => DeploymentStatus::Pending,
                    'deployment_details' => ['detail' => 'Cancel request queued.'],
                ])->save();

                DefenderDeployment::dispatch($record->id, DefenderDeployment::ACTION_CANCEL);
            }
        )
            ->authorize('cancel')
            ->color('pink');
    }

    public static function cancelDefenderBulkButton()
    {
        return self::bulkButton(
            'cancel_bulk_button',
            __('tables.defender.buttons.cancel_bulk_button'),
            Heroicon::OutlinedArchiveBoxXMark,
            function ($records) {
                foreach ($records as $record) {
                    if ($record->deployment_status !== DeploymentStatus::Successful) {
                        continue;
                    }

                    $record->forceFill([
                        'deployment_status' => DeploymentStatus::Pending,
                        'deployment_details' => ['detail' => 'Cancel request queued.'],
                    ])->save();

                    DefenderDeployment::dispatch($record->id, DefenderDeployment::ACTION_CANCEL);
                }
            }
        )
            ->authorize('cancelAny')
            ->color('pink')
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
                        DeploymentStatus::Processing,
                        DeploymentStatus::Successful,
                    ], true)) {
                        continue;
                    }
                    $record->delete();
                }
            });
    }

    public static function followDefenderButton()
    {
        return self::button(
            'follow_button',
            __('forms.defender.buttons.follow'),
            Heroicon::OutlinedBarsArrowDown,
            function ($record, $set) {
                $state = '';

                if (! $record?->getKey()) {
                    $set('log', $state);

                    return;
                }

                try {
                    $response = Orchestrator::follow((string) $record->getKey());
                    $state = $response->json();

                    if ($state === null) {
                        $state = [
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ];
                    }
                } catch (Throwable $exception) {
                    $state = [
                        'detail' => __('forms.defender.extras.log.failed_to_follow'),
                        'exception' => $exception::class,
                        'message' => $exception->getMessage(),
                    ];
                }

                if (! is_string($state)) {
                    $state = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                        ?: (string) print_r($state, true);
                }

                $set('log', $state);
            }
        )
            ->tooltip(__('tables.defender.buttons.tooltips.follow'))
            ->authorize('follow')
            ->color('sky');
    }
}
