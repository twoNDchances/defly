<?php

namespace App\Traits\Filament\Specifics\Principle;

use App\Enums\Defender\DeploymentStatus;
use App\Enums\Principle\ValidationStatus;
use App\Jobs\PrincipleValidation;
use App\Models\Defender;
use App\Services\Lock;
use App\Traits\Filament\Generals\Components\Button;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

trait PrincipleButton
{
    use Button;

    protected static function ownerDefenderIsDeployed(?Defender $defender): bool
    {
        return $defender instanceof Defender
            && $defender->deployment_status === DeploymentStatus::Successful;
    }

    public static function attachPolicesAndLockButton()
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

    public static function detachPrinciplesAndUnlockButton()
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

    public static function detachPrinciplesAndUnlockBulkButton()
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

    public static function validatePrincipleButton()
    {
        return self::button(
            'validate_button',
            __('tables.principle.buttons.validate_button'),
            Heroicon::OutlinedCheck,
            function ($record) {
                $record->validation_status = ValidationStatus::Pending;
                $record->save();
                PrincipleValidation::dispatch($record->id);
            },
        )
            ->authorize('validate')
            ->color('cyan');
    }

    public static function clonePrincipleButton()
    {
        return self::cloneButton()
            ->action(function ($record) {
                $clone = $record->replicate();
                $suffix = Str::random(6);
                $clone->name = "$record->name-$suffix";
                $clone->is_locked = false;
                $clone->validation_status = null;
                $clone->validation_details = null;
                $clone->save();
                $clone->labels()->sync($record->labels()->pluck('id')->all());
            });
    }

    public static function validatePrincipleBulkButton()
    {
        return self::bulkButton(
            'validate_bulk_button',
            __('tables.principle.buttons.validate_bulk_button'),
            Heroicon::OutlinedCheck,
            function ($records) {
                foreach ($records as $record) {
                    if (in_array($record->validation_status, [ValidationStatus::Pending, ValidationStatus::Validating])) {
                        continue;
                    }
                    if ($record->is_locked) {
                        continue;
                    }
                    $record->validation_status = ValidationStatus::Pending;
                    $record->save();
                    PrincipleValidation::dispatch($record->id);
                }
            }
        )
            ->authorize('validateAny')
            ->color('cyan')
            ->chunkSelectedRecords(100)
            ->deselectRecordsAfterCompletion();
    }

    public static function deleteUnlockedBulkButton()
    {
        return self::deleteBulkButton()
            ->action(function ($records) {
                foreach ($records as $record) {
                    if ($record->hasAttribute('is_locked') && $record->is_locked == true) {
                        continue;
                    }

                    if (in_array($record->validation_status, [
                        ValidationStatus::Pending,
                        ValidationStatus::Validating,
                    ], true)) {
                        continue;
                    }

                    $record->delete();
                }
            });
    }

    public static function applyPrincipleButton(?Defender $defender = null)
    {
        return self::button(
            'apply_button',
            'Apply',
            Heroicon::OutlinedArrowUpOnSquareStack,
            function ($record) use ($defender) {
                if (! self::ownerDefenderIsDeployed($defender)) {
                    return;
                }

            },
        )
            ->color('sky')
            ->visible(fn () => self::ownerDefenderIsDeployed($defender))
            ->authorize('apply');
    }

    public static function applyPrincipleBulkButton(?Defender $defender = null)
    {
        return self::bulkButton(
            'apply_bulk_button',
            'Apply selected items',
            Heroicon::OutlinedArrowUpOnSquareStack,
            function ($records) use ($defender) {
                if (! self::ownerDefenderIsDeployed($defender)) {
                    return;
                }

                foreach ($records as $record) {

                }
            },
        )
            ->color('sky')
            ->visible(fn () => self::ownerDefenderIsDeployed($defender))
            ->authorize('applyAny');
    }

    public static function revokePrincipleButton(?Defender $defender = null)
    {
        return self::button(
            'revoke_button',
            'Apply',
            Heroicon::OutlinedArrowUturnLeft,
            function ($record) use ($defender) {
                if (! self::ownerDefenderIsDeployed($defender) || ! $record->is_applied) {
                    return;
                }

            },
        )
            ->color('pink')
            ->visible(fn ($record) => self::ownerDefenderIsDeployed($defender) && $record->is_applied)
            ->authorize('revoke');
    }

    public static function revokePrincipleBulkButton(?Defender $defender = null)
    {
        return self::bulkButton(
            'revoke_bulk_button',
            'Revoke selected items',
            Heroicon::OutlinedArrowUturnLeft,
            function ($records) use ($defender) {
                if (! self::ownerDefenderIsDeployed($defender)) {
                    return;
                }

                foreach ($records as $record) {
                    if (! $record->is_applied) {
                        continue;
                    }
                }
            },
        )
            ->color('pink')
            ->visible(fn () => self::ownerDefenderIsDeployed($defender))
            ->authorize('revokeAny');
    }
}
