<?php

namespace App\Filament\Components;

use Filament\Actions;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

trait Action
{
    public static function createAction()
    {
        return Actions\CreateAction::make()->icon(fn () => Heroicon::OutlinedPlus);
    }

    public static function attachAction()
    {
        return Actions\AttachAction::make()->icon(fn () => Heroicon::OutlinedLink);
    }

    public static function viewAction()
    {
        return Actions\ViewAction::make()->icon(fn () => Heroicon::OutlinedEye)->modalWidth(Width::SevenExtraLarge);
    }

    public static function editAction()
    {
        return Actions\EditAction::make()->icon(fn () => Heroicon::OutlinedPencilSquare);
    }

    public static function detachAction()
    {
        return Actions\DetachAction::make()->icon(fn () => Heroicon::OutlinedXMark);
    }

    public static function deleteAction()
    {
        return Actions\DeleteAction::make()->icon(fn () => Heroicon::OutlinedTrash);
    }

    public static function action(string $name, $label = null, $icon = null, $action = null)
    {
        return Actions\Action::make($name)
        ->label($label)
        ->icon($icon)
        ->action($action);
    }

    public static function actionGroup($view = true, $edit = true, $delete = true, $more = [])
    {
        $actionGroup = [];
        if ($view)
        {
            $actionGroup[] = self::viewAction();
        }
        if ($edit)
        {
            $actionGroup[] = self::editAction();
        }
        if (count($more) > 0)
        {
            foreach ($more as $action)
            {
                $actionGroup[] = $action;
            }
        }
        if ($delete)
        {
            $actionGroup[] = self::deleteAction();
        }
        return Actions\ActionGroup::make($actionGroup);
    }
}
