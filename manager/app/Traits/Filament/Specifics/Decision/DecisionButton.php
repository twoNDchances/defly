<?php

namespace App\Traits\Filament\Specifics\Decision;

use App\Enums\Defender\DeploymentStatus;
use App\Jobs\DefenderCommunication;
use App\Models\Defender;
use App\Services\Identification;
use App\Services\Lock;
use App\Services\Logger;
use App\Services\Security;
use App\Traits\Filament\Generals\Components\Button;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Http;
use Throwable;

trait DecisionButton
{
    use Button;

    protected static function ownerDefenderIsDeployed(?Defender $defender): bool
    {
        return $defender instanceof Defender
            && $defender->deployment_status === DeploymentStatus::Successful
            && Security::canOperateDefender($defender);
    }

    public static function testRequestButton()
    {
        return self::button(
            'test_request_button',
            __('forms.decision.buttons.test_request_button'),
            Heroicon::OutlinedBeaker,
            function ($state, $action) {
                if (blank($state)) {
                    Notification::make()
                        ->warning()
                        ->title(__('forms.decision.buttons.test_request_button_empty'))
                        ->send();

                    $action->failure();

                    return;
                }

                try {
                    Http::timeout(10)->get($state);
                } catch (Throwable $exception) {
                    Notification::make()
                        ->warning()
                        ->title(__('forms.decision.buttons.test_request_button_failed'))
                        ->body($exception->getMessage())
                        ->send();

                    $action->failure();
                }
            },
        )
            ->failureNotification(null)
            ->successNotificationTitle(__('forms.decision.buttons.test_request_button_success'));
    }

    public static function attachDecisionsAndLockButton()
    {
        return self::attachAndLockButton()
            ->after(function ($data, $table) {
                $recordIds = $data['recordId'] ?? null;

                if (blank($recordIds)) {
                    return;
                }

                $recordIds = is_array($recordIds) ? $recordIds : [$recordIds];

                $relatedModelClass = $table->getRelationship()->getRelated()::class;
                Lock::syncByRelationship($relatedModelClass, $recordIds);

                $ownerRecord = $table->getRelationship()->getParent();

                if (! $ownerRecord instanceof Defender) {
                    return;
                }

                // $ownerRecord->forceFill([
                //     'deployment_status' => null,
                //     'deployment_details' => null,
                // ])->save();

                $livewire = $table->getLivewire();
                $livewire
                    ->dispatch('refresh-form-data', statePaths: ['deployment_status', 'deployment_details'])
                    ->to($livewire->getPageClass());
            });
    }

    public static function detachDecisionsAndUnlockButton()
    {
        return self::detachAndUnlockButton()
            ->after(function ($record, $table) {
                if (! $record) {
                    return;
                }

                Lock::syncByRelationship($record::class, $record->getKey());

                $ownerRecord = $table->getRelationship()->getParent();

                if (! $ownerRecord instanceof Defender) {
                    return;
                }

                // $ownerRecord->forceFill([
                //     'deployment_status' => null,
                //     'deployment_details' => null,
                // ])->save();

                $livewire = $table->getLivewire();
                $livewire
                    ->dispatch('refresh-form-data', statePaths: ['deployment_status', 'deployment_details'])
                    ->to($livewire->getPageClass());
            });
    }

    public static function detachDecisionsAndUnlockBulkButton()
    {
        return self::detachAndUnlockBulkButton()
            ->after(function ($records, $table) {
                if (blank($records)) {
                    return;
                }

                $ownerRecord = $table->getRelationship()->getParent();

                if (! $ownerRecord instanceof Defender) {
                    return;
                }

                // $ownerRecord->forceFill([
                //     'deployment_status' => null,
                //     'deployment_details' => null,
                // ])->save();

                $livewire = $table->getLivewire();
                $livewire
                    ->dispatch('refresh-form-data', statePaths: ['deployment_status', 'deployment_details'])
                    ->to($livewire->getPageClass());
            });
    }

    public static function implementDecisionButton(?Defender $defender = null)
    {
        return self::button(
            'implement_button',
            __('tables.decision.buttons.implement'),
            Heroicon::OutlinedBolt,
            function ($record) use ($defender) {
                if (! self::ownerDefenderIsDeployed($defender)) {
                    return;
                }

                DefenderCommunication::dispatch(
                    $defender->id,
                    [$record->id],
                    DefenderCommunication::ACTION_IMPLEMENT,
                    Identification::getEmail(),
                );
                Logger::log($record, 'implement');
            },
        )
            ->color('orange')
            ->visible(fn () => self::ownerDefenderIsDeployed($defender))
            ->authorize('implement');
    }

    public static function implementDecisionBulkButton(?Defender $defender = null)
    {
        return self::bulkButton(
            'implement_bulk_button',
            __('tables.decision.buttons.implementAny'),
            Heroicon::OutlinedBolt,
            function ($records) use ($defender) {
                if (! self::ownerDefenderIsDeployed($defender)) {
                    return;
                }

                $recordIds = $records->pluck('id')->all();
                if ($recordIds === []) {
                    return;
                }

                DefenderCommunication::dispatch(
                    $defender->id,
                    $recordIds,
                    DefenderCommunication::ACTION_IMPLEMENT,
                    Identification::getEmail(),
                );
            },
        )
            ->color('orange')
            ->visible(fn () => self::ownerDefenderIsDeployed($defender))
            ->authorize('implementAny')
            ->deselectRecordsAfterCompletion();
    }

    public static function suspendDecisionButton(?Defender $defender = null)
    {
        return self::button(
            'suspend_button',
            __('tables.decision.buttons.suspend'),
            Heroicon::OutlinedBoltSlash,
            function ($record) use ($defender) {
                if (! self::ownerDefenderIsDeployed($defender) || ! $record->pivot?->is_implemented) {
                    return;
                }

                DefenderCommunication::dispatch(
                    $defender->id,
                    [$record->id],
                    DefenderCommunication::ACTION_SUSPEND,
                    Identification::getEmail(),
                );
                Logger::log($record, 'suspend');
            },
        )
            ->color('warning')
            ->visible(fn ($record) => self::ownerDefenderIsDeployed($defender) && $record->pivot?->is_implemented)
            ->authorize('suspend');
    }

    public static function suspendDecisionBulkButton(?Defender $defender = null)
    {
        return self::bulkButton(
            'suspend_bulk_button',
            __('tables.decision.buttons.suspendAny'),
            Heroicon::OutlinedBoltSlash,
            function ($records) use ($defender) {
                if (! self::ownerDefenderIsDeployed($defender)) {
                    return;
                }

                $recordIds = $records
                    ->filter(fn ($record) => $record->pivot?->is_implemented)
                    ->pluck('id')
                    ->all();
                if ($recordIds === []) {
                    return;
                }

                DefenderCommunication::dispatch(
                    $defender->id,
                    $recordIds,
                    DefenderCommunication::ACTION_SUSPEND,
                    Identification::getEmail(),
                );
            },
        )
            ->color('warning')
            ->visible(fn () => self::ownerDefenderIsDeployed($defender))
            ->authorize('suspendAny')
            ->deselectRecordsAfterCompletion();
    }
}
