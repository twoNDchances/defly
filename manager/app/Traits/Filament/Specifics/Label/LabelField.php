<?php

namespace App\Traits\Filament\Specifics\Label;

use App\Traits\Filament\Generals\Components\Field;

trait LabelField
{
    use Field;

    public static function name()
    {
        return self::textInput(
            'name',
            __('models.label.fields.name'),
            __('forms.label.text_examples.name'),
        )
            ->helperText(__('forms.label.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->alphaDash()
            ->required();
    }

    public static function color()
    {
        return self::colorPicker(
            'color',
            __('models.label.fields.color'),
        )
            ->helperText(__('forms.label.descriptions.color'))
            ->required();
    }

    public static function description()
    {
        return self::textArea(
            'description',
            __('models.label.fields.description'),
            __('forms.label.text_examples.description'),
        )
            ->helperText(__('forms.label.descriptions.description'));
    }
}
