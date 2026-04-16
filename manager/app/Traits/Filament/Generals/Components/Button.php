<?php

namespace App\Traits\Filament\Generals\Components;

use Filament\Actions;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachBulkAction;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

trait Button
{
    public static function createButton()
    {
        return Actions\CreateAction::make()
            ->icon(fn () => Heroicon::OutlinedPlus)
            ->mutateDataUsing(function ($data) {
                if (method_exists(static::class, 'createForm')) {
                    $data = static::createForm($data);
                }
                if (method_exists(static::class, 'saveForm')) {
                    $data = static::saveForm($data);
                }

                return $data;
            });
    }

    public static function attachButton()
    {
        return Actions\AttachAction::make()->icon(fn () => Heroicon::OutlinedLink)
            ->preloadRecordSelect()
            ->multiple();
    }

    public static function attachAndLockButton()
    {
        return self::attachButton()
            ->after(function ($data, $table) {
                $recordIds = $data['recordId'] ?? null;

                if (blank($recordIds)) {
                    return;
                }

                $recordIds = is_array($recordIds) ? $recordIds : [$recordIds];

                $records = $table->getRelationship()
                    ->getRelated()
                    ->newQuery()
                    ->whereKey($recordIds)
                    ->get();

                foreach ($records as $record) {
                    if (! $record->hasAttribute('locked')) {
                        continue;
                    }

                    $record->locked = true;
                    $record->save();
                }
            });
    }

    public static function viewButton()
    {
        return Actions\ViewAction::make()->icon(fn () => Heroicon::OutlinedEye)->modalWidth(Width::SevenExtraLarge);
    }

    public static function editButton()
    {
        return Actions\EditAction::make()
            ->icon(fn () => Heroicon::OutlinedPencilSquare)
            ->mutateDataUsing(function ($data) {
                if (method_exists(static::class, 'editForm')) {
                    $data = static::editForm($data);
                }
                if (method_exists(static::class, 'saveForm')) {
                    $data = static::saveForm($data);
                }

                return $data;
            });
    }

    public static function detachButton()
    {
        return Actions\DetachAction::make()->icon(fn () => Heroicon::OutlinedXMark);
    }

    public static function detachAndUnlockButton()
    {
        return self::detachButton()
            ->after(function ($record) {
                if (! $record->hasAttribute('locked')) {
                    return;
                }

                $record->locked = false;
                $record->save();
            });
    }

    public static function deleteButton()
    {
        return Actions\DeleteAction::make()->icon(fn () => Heroicon::OutlinedTrash);
    }

    public static function button(string $name, $label = null, $icon = null, $action = null)
    {
        return Actions\Action::make($name)
            ->label($label)
            ->icon($icon)
            ->action($action);
    }

    public static function buttonGroup($view = true, $edit = true, $delete = true, $more = [])
    {
        $actionGroup = [];
        if ($view) {
            $actionGroup[] = self::viewButton();
        }
        if ($edit) {
            $actionGroup[] = self::editButton();
        }
        if (count($more) > 0) {
            foreach ($more as $action) {
                $actionGroup[] = $action;
            }
        }
        if ($delete) {
            $actionGroup[] = self::deleteButton();
        }

        return Actions\ActionGroup::make($actionGroup);
    }

    public static function bulkButton(string $name, $label = null, $icon = null, $action = null)
    {
        return Actions\BulkAction::make($name)
            ->label($label)
            ->icon($icon)
            ->action($action);
    }

    public static function deleteBulkButton()
    {
        return DeleteBulkAction::make()->chunkSelectedRecords(100);
    }

    public static function deleteUnlockedBulkButton()
    {
        return self::deleteBulkButton()
            ->action(function ($records) {
                foreach ($records as $record) {
                    if ($record->hasAttribute('locked') && $record->locked == false) {
                        $record->delete();
                    }
                }
            });
    }

    public static function detachBulkButton()
    {
        return DetachBulkAction::make()->chunkSelectedRecords(100);
    }

    public static function detachAndUnlockBulkButton()
    {
        return self::bulkButton(
            'detach_and_unlock_bulk_button',
            __('filament-actions::detach.multiple.label'),
            Heroicon::OutlinedXMark,
            function ($records, $table) {
                $relationship = $table->getRelationship();
                $relationshipPivotAccessor = $relationship->getPivotAccessor();

                foreach ($records as $record) {
                    if ($table->allowsDuplicates()) {
                        $record->getRelationValue($relationshipPivotAccessor)->delete();
                    } else {
                        $relationship->detach($record);
                    }

                    if (! $record->hasAttribute('locked')) {
                        continue;
                    }

                    $record->locked = false;
                    $record->save();
                }
            }
        )
            ->requiresConfirmation()
            ->color('danger')
            ->deselectRecordsAfterCompletion();
    }

    public static function bulkButtonGroup($delete = true, $more = [])
    {
        $bulkActionGroup = [];
        if (count($more) > 0) {
            foreach ($more as $bulkAction) {
                $bulkActionGroup[] = $bulkAction;
            }
        }
        if ($delete) {
            $bulkActionGroup[] = self::deleteBulkButton();
        }

        return Actions\BulkActionGroup::make($bulkActionGroup);
    }

    public static function cloneButton()
    {
        return self::button(
            'clone_button',
            __('tables.generals.specials.buttons.clone'),
            Heroicon::OutlinedSquare2Stack,
            function ($record) {
                $clone = $record->replicate();
                $suffix = Str::random(6);
                $clone->name = "$record->name-$suffix";
                if ($clone->hasAttribute('locked')) {
                    $clone->locked = false;
                }
                $clone->save();
                $clone->labels()->sync($record->labels()->pluck('id')->all());
            })
            ->requiresConfirmation()
            ->color('gray');
    }
}
