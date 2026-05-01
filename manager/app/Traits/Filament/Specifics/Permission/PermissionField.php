<?php

namespace App\Traits\Filament\Specifics\Permission;

use App\Traits\Filament\Generals\Components\Field;
use App\Traits\Validators\PermissionValidator;

trait PermissionField
{
    use Field, PermissionButton, PermissionData, PermissionValidator;

    public static function setName()
    {
        return self::textInput('name', __('models.permission.fields.name'), __('forms.permission.text_examples.name'))
            ->helperText(__('forms.permission.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->required()
            ->rules(fn ($livewire) => self::validateName(ignore: $livewire->record ?? null));
    }

    public static function setAppliedFor()
    {
        return self::select('applied_for', __('models.permission.fields.applied_for'))
            ->helperText(__('forms.permission.descriptions.applied_for'))
            ->options(self::permissionModelOptions())
            ->searchable()
            ->required()
            ->rules(self::validateAppliedFor())
            ->reactive()
            ->afterStateUpdated(fn ($set) => $set('action', null));
    }

    public static function setAction()
    {
        return self::select('action', __('models.permission.fields.action'))
            ->helperText(__('forms.permission.descriptions.action'))
            ->options(function ($get) {
                $appliedFor = $get('applied_for');
                if (! $appliedFor) {
                    return [];
                }

                return self::permissionList()[$appliedFor] ?? [];
            })
            ->searchable()
            ->required()
            ->rules(self::validateAction());
    }

    public static function setDescriptionField()
    {
        return self::setDescription();
    }
}
