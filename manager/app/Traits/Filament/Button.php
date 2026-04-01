<?php

namespace App\Traits\Filament;

use Filament\Actions;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

trait Button
{
    public static function createButton()
    {
        return Actions\CreateAction::make()->icon(fn () => Heroicon::OutlinedPlus);
    }

    public static function attachButton()
    {
        return Actions\AttachAction::make()->icon(fn () => Heroicon::OutlinedLink);
    }

    public static function viewButton()
    {
        return Actions\ViewAction::make()->icon(fn () => Heroicon::OutlinedEye)->modalWidth(Width::SevenExtraLarge);
    }

    public static function editButton()
    {
        return Actions\EditAction::make()->icon(fn () => Heroicon::OutlinedPencilSquare);
    }

    public static function detachButton()
    {
        return Actions\DetachAction::make()->icon(fn () => Heroicon::OutlinedXMark);
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

    public static function bulkButtonGroup($delete = true, $more = [])
    {
        $bulkActionGroup = [];
        if (count($more) > 0) {
            foreach ($more as $bulkAction) {
                $bulkActionGroup[] = $bulkAction;
            }
        }
        if ($delete) {
            $bulkActionGroup[] = Actions\DeleteBulkAction::make();
        }

        return Actions\BulkActionGroup::make($bulkActionGroup);
    }
}
