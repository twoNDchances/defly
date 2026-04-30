<?php

namespace App\Traits\Filament\Specifics\Decision;

use App\Enums\Defender\DeploymentStatus;
use App\Models\Defender;
use App\Services\Lock;
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
            && $defender->deployment_status === DeploymentStatus::Successful;
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

                $ownerRecord->forceFill([
                    'deployment_status' => null,
                    'deployment_details' => null,
                ])->save();

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

                $ownerRecord->forceFill([
                    'deployment_status' => null,
                    'deployment_details' => null,
                ])->save();

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

                $ownerRecord->forceFill([
                    'deployment_status' => null,
                    'deployment_details' => null,
                ])->save();

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
            'Implement',
            Heroicon::OutlinedBolt,
            function ($record) use ($defender) {
                if (! self::ownerDefenderIsDeployed($defender)) {
                    return;
                }

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
            'Implement selected items',
            Heroicon::OutlinedBolt,
            function ($records) use ($defender) {
                if (! self::ownerDefenderIsDeployed($defender)) {
                    return;
                }

                foreach ($records as $record) {

                }
            },
        )
            ->color('orange')
            ->visible(fn () => self::ownerDefenderIsDeployed($defender))
            ->authorize('implementAny');
    }

    public static function suspendDecisionButton(?Defender $defender = null)
    {
        return self::button(
            'suspend_button',
            'Suspend',
            Heroicon::OutlinedBoltSlash,
            function ($record) use ($defender) {
                if (! self::ownerDefenderIsDeployed($defender) || ! $record->is_implemented) {
                    return;
                }

            },
        )
            ->color('yellow')
            ->visible(fn ($record) => self::ownerDefenderIsDeployed($defender) && $record->is_implemented)
            ->authorize('suspend');
    }

    public static function suspendDecisionBulkButton(?Defender $defender = null)
    {
        return self::bulkButton(
            'suspend_bulk_button',
            'Suspend selected items',
            Heroicon::OutlinedBoltSlash,
            function ($records) use ($defender) {
                if (! self::ownerDefenderIsDeployed($defender)) {
                    return;
                }

                foreach ($records as $record) {
                    if (! $record->is_implemented) {
                        continue;
                    }
                }
            },
        )
            ->color('yellow')
            ->visible(fn () => self::ownerDefenderIsDeployed($defender))
            ->authorize('suspendAny');
    }
}
