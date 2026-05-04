<?php

namespace App\Traits\Filament\Specifics\Pattern;

use App\Enums\Type;
use App\Traits\Filament\Generals\Components\Field;
use App\Traits\Validators\PatternValidator;

trait PatternField
{
    use Field, PatternButton, PatternData, PatternValidator;

    public static function setName()
    {
        return self::textInput('name', __('models.pattern.fields.name'))
            ->helperText(__('forms.pattern.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->required()
            ->rules(fn ($record) => self::validateName(ignore: $record?->getKey()));
    }

    public static function setPhase()
    {
        return self::toggleButtons(
            'phase',
            __('models.pattern.fields.phase'),
            self::phaseOptionsAndColors(),
        )
            ->helperText(__('forms.pattern.descriptions.phase'))
            ->required()
            ->rules(self::validatePhase());
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
            ->helperText(__('forms.pattern.descriptions.type'))
            ->required()
            ->rules(self::validateType());
    }

    public static function setDatatype()
    {
        return self::toggleButtons(
            'datatype',
            __('models.pattern.fields.datatype'),
            self::datatypeOptionsAndColors(),
        )
            ->helperText(__('forms.pattern.descriptions.datatype'))
            ->required()
            ->rules(self::validateDatatype());
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
            ->rules(self::validateTargets())
            ->relationship('targets', 'name');
    }
}
