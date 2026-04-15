<?php

namespace App\Traits\Filament\Specifics\Group;

use App\Traits\Filament\Generals\Components\Field;

trait GroupField
{
    use Field, GroupButton, GroupData;

    public static function setName()
    {
        return self::textInput(
            'name',
            __('models.group.fields.name'),
            __('forms.group.text_examples.name'),
        )
            ->helperText(__('forms.group.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->alphaDash()
            ->required();
    }

    public static function setDescriptionField()
    {
        return self::setDescription();
    }
}
