<?php

namespace App\Traits\Filament\Specifics\Label;

use App\Traits\Filament\Generals\Components\Field;
use App\Traits\Validators\LabelValidator;

trait LabelField
{
    use Field, LabelButton, LabelData, LabelValidator;

    public static function setName()
    {
        return self::textInput(
            'name',
            __('models.label.fields.name'),
            __('forms.label.text_examples.name'),
        )
            ->helperText(__('forms.label.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->alphaDash()
            ->required()
            ->rules(fn ($record) => self::validateName(ignore: $record?->getKey()));
    }

    public static function setColor()
    {
        return self::colorPicker(
            'color',
            __('models.label.fields.color'),
        )
            ->helperText(__('forms.label.descriptions.color'))
            ->required()
            ->rules(self::validateColor());
    }

    public static function setDescriptionField()
    {
        return self::setDescription();
    }
}
