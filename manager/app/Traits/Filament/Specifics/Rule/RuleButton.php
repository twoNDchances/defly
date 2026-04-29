<?php

namespace App\Traits\Filament\Specifics\Rule;

use App\Models\Principle;
use App\Services\Lock;
use App\Traits\Filament\Generals\Components\Button;

trait RuleButton
{
    use Button;

    public static function attachRulesAndLockButton()
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

                if (! $ownerRecord instanceof Principle) {
                    return;
                }

                $ownerRecord->forceFill([
                    'validation_status' => null,
                    'validation_details' => null,
                ])->save();

                $livewire = $table->getLivewire();
                $livewire
                    ->dispatch('refresh-form-data', statePaths: ['validation_status', 'validation_details'])
                    ->to($livewire->getPageClass());
            });
    }

    public static function detachRulesAndUnlockButton()
    {
        return self::detachAndUnlockButton()
            ->after(function ($record, $table) {
                if (! $record) {
                    return;
                }

                Lock::syncByRelationship($record::class, $record->getKey());

                $ownerRecord = $table->getRelationship()->getParent();

                if (! $ownerRecord instanceof Principle) {
                    return;
                }

                $ownerRecord->forceFill([
                    'validation_status' => null,
                    'validation_details' => null,
                ])->save();

                $livewire = $table->getLivewire();
                $livewire
                    ->dispatch('refresh-form-data', statePaths: ['validation_status', 'validation_details'])
                    ->to($livewire->getPageClass());
            });
    }

    public static function detachRulesAndUnlockBulkButton()
    {
        return self::detachAndUnlockBulkButton()
            ->after(function ($records, $table) {
                if (blank($records)) {
                    return;
                }

                $ownerRecord = $table->getRelationship()->getParent();

                if (! $ownerRecord instanceof Principle) {
                    return;
                }

                $ownerRecord->forceFill([
                    'validation_status' => null,
                    'validation_details' => null,
                ])->save();

                $livewire = $table->getLivewire();
                $livewire
                    ->dispatch('refresh-form-data', statePaths: ['validation_status', 'validation_details'])
                    ->to($livewire->getPageClass());
            });
    }
}
