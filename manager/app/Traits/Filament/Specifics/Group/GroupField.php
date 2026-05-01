<?php

namespace App\Traits\Filament\Specifics\Group;

use App\Traits\Filament\Generals\Components\Field;
use App\Traits\Validators\GroupValidator;

trait GroupField
{
    use Field, GroupButton, GroupData, GroupValidator;

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
            ->required()
            ->rules(fn ($livewire) => self::validateName(ignore: $livewire->record ?? null));
    }

    public static function setDescriptionField()
    {
        return self::setDescription();
    }
}
