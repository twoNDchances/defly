<?php

namespace App\Traits\Filament\Specifics\Pattern;

use App\Enums\Type;
use App\Traits\Filament\Generals\Components\Field;

trait PatternField
{
    use Field;
    use PatternData;

    public static function name()
    {
        return self::textInput('name', __('models.pattern.fields.name'))
        ->helperText(__('forms.pattern.descriptions.name'));
    }

    public static function phase()
    {
        return self::toggleButtons(
            'phase',
            __('models.pattern.fields.phase'),
            self::phaseOptionsAndColors(),
        )
        ->helperText(__('forms.pattern.descriptions.phase'));
    }

    public static function type()
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

    public static function datatype()
    {
        return self::toggleButtons(
            'datatype',
            __('models.pattern.fields.datatype'),
            self::datatypeOptionsAndColors(),
        )
        ->helperText(__('forms.pattern.descriptions.datatype'));
    }

    public static function description()
    {
        return self::textArea(
            'description',
            __('models.commons.description'),
        )
        ->helperText(__('forms.pattern.descriptions.description'));
    }

    public static function targets()
    {
        return self::select('targets', __(''))
        ->multiple()
        ->relationship('targets', 'name');
    }
}
