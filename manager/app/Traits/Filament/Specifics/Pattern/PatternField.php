<?php

namespace App\Traits\Filament\Specifics\Pattern;

use App\Enums\Type;
use App\Traits\Filament\Generals\Components\Field;

trait PatternField
{
    use Field, PatternButton, PatternData;

    public static function setName()
    {
        return self::textInput('name', __('models.pattern.fields.name'))
            ->helperText(__('forms.pattern.descriptions.name'));
    }

    public static function setPhase()
    {
        return self::toggleButtons(
            'phase',
            __('models.pattern.fields.phase'),
            self::phaseOptionsAndColors(),
        )
            ->helperText(__('forms.pattern.descriptions.phase'));
    }

    public static function setType()
    {
        $typeOptions = self::typeOptionsAndColors()['options'];
        $typeColors = self::typeOptionsAndColors()['colors'];
        $getter = Type::Getter->value;
        unset($typeOptions[$getter], $typeColors[$getter]);

        return self::toggleButtons(
            'type',
            __('models.pattern.fields.type'),
            [
                'options' => $typeOptions,
                'colors' => $typeColors,
            ],
        )
            ->helperText(__('forms.pattern.descriptions.type'));
    }

    public static function setDatatype()
    {
        return self::toggleButtons(
            'datatype',
            __('models.pattern.fields.datatype'),
            self::datatypeOptionsAndColors(),
        )
            ->helperText(__('forms.pattern.descriptions.datatype'));
    }

    public static function setDescriptionField()
    {
        return self::setDescription();
    }

    public static function setTargets()
    {
        return self::select('targets', __('models.pattern.fields.targets'))
            ->helperText(__('forms.pattern.descriptions.targets'))
            ->multiple()
            ->relationship('targets', 'name');
    }
}
