<?php

namespace App\Traits\Filament\Specifics\Decision;

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
}
