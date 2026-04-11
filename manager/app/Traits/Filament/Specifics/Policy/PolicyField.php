<?php

namespace App\Traits\Filament\Specifics\Policy;

use App\Traits\Filament\Generals\Components\Field;

trait PolicyField
{
    use Field, PolicyButton, PolicyData;

    public static function name()
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

    public static function description()
    {
        return self::textArea(
            'description',
            __('models.commons.description'),
            __('forms.policy.text_examples.description'),
        )
            ->helperText(__('forms.policy.descriptions.description'));
    }
}
