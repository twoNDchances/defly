<?php

namespace App\Traits\Filament\Specifics\Permission;

use App\Services\Security;
use App\Traits\Filament\Generals\Components\Field;

trait PermissionField
{
    use Field;
    use PermissionButton;

    public static function name()
    {
        return self::textInput('name', __('models.permission.fields.name'), __('forms.permission.text_examples.name'))
            ->helperText(__('forms.permission.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->required();
    }

    public static function appliedFor()
    {
        $models = array_keys(Security::generatePermissionList(true));

        return self::select('applied_for', __('models.permission.fields.applied_for'))
            ->helperText(__('forms.permission.descriptions.applied_for'))
            ->options(array_combine($models, $models))
            ->searchable()
            ->required()
            ->reactive()
            ->afterStateUpdated(fn ($set) => $set('action', null));
    }

    public static function action()
    {
        return self::select('action', __('models.permission.fields.action'))
            ->helperText(__('forms.permission.descriptions.action'))
            ->options(function ($get) {
                $appliedFor = $get('applied_for');
                if (! $appliedFor) {
                    return [];
                }

                return Security::generatePermissionList(true)[$appliedFor] ?? [];
            })
            ->searchable()
            ->required();
    }

    public static function description()
    {
        return self::textArea(
            'description',
            __('models.permission.fields.description'),
            __('forms.permission.text_examples.description'),
        )
            ->helperText(__('forms.permission.descriptions.description'));
    }
}
