<?php

namespace App\Traits\Filament\Specifics\Policy;

use App\Traits\Filament\Generals\Components\Field;

trait PolicyField
{
    use Field, PolicyButton, PolicyData;

    public static function setName()
    {
        return self::textInput(
            'name',
            __('models.policy.fields.name'),
            __('forms.policy.text_examples.name'),
        )
            ->helperText(__('forms.policy.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->alphaDash()
            ->required();
    }

    public static function setDescriptionField()
    {
        return self::setDescription();
    }
}
