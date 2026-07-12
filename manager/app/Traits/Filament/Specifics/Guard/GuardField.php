<?php

namespace App\Traits\Filament\Specifics\Guard;

use App\Traits\Filament\Generals\Components\Field;
use App\Traits\Validators\GuardValidator;

trait GuardField
{
    use Field, GuardButton, GuardData, GuardValidator;

    public static function setName()
    {
        return self::textInput(
            'name',
            __('models.guard.fields.name'),
            __('forms.guard.text_examples.name'),
        )
            ->helperText(__('forms.guard.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->alphaDash()
            ->required()
            ->rules(fn ($record) => self::validateName(ignore: $record?->getKey()));
    }

    public static function setExpiredAt()
    {
        return self::datetimePicker('expired_at', __('models.guard.fields.expired_at'))
            ->helperText(__('forms.guard.descriptions.expired_at'))
            ->seconds(false)
            ->nullable()
            ->rules(self::validateExpiredAt('nullable'));
    }

    public static function setDescriptionField()
    {
        return self::setDescription();
    }
}
