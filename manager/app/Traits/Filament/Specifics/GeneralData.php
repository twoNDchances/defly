<?php

namespace App\Traits\Filament\Specifics;

use App\Enums\Datatype;
use App\Enums\Phase;
use App\Enums\Type;

trait GeneralData
{
    public static function phaseOptionsAndColors()
    {
        return [
            'options' => [
                Phase::One->value => __('models.commons.phase.1'),
                Phase::Two->value => __('models.commons.phase.2'),
                Phase::Three->value => __('models.commons.phase.3'),
                Phase::Four->value => __('models.commons.phase.4'),
                Phase::Five->value => __('models.commons.phase.5'),
                Phase::Six->value => __('models.commons.phase.6'),
            ],
            'colors' => [
                Phase::One->value => 'purple',
                Phase::Two->value => 'primary',
                Phase::Three->value => 'info',
                Phase::Four->value => 'rose',
                Phase::Five->value => 'danger',
                Phase::Six->value => 'pink',
            ],
        ];
    }

    public static function typeOptionsAndColors()
    {
        return [
            'options' => [
                Type::Getter->value => __('models.commons.type.getter'),
                Type::Full->value => __('models.commons.type.full'),
                Type::Header->value => __('models.commons.type.header'),
                Type::Meta->value => __('models.commons.type.meta'),
                Type::Query->value => __('models.commons.type.query'),
                Type::Body->value => __('models.commons.type.body'),
                Type::File->value => __('models.commons.type.file'),
            ],
            'colors' => [
                Type::Getter->value => 'purple',
                Type::Full->value => 'gray',
                Type::Header->value => 'info',
                Type::Meta->value => 'danger',
                Type::Query->value => 'warning',
                Type::Body->value => 'sky',
                Type::File->value => 'teal',
            ],
        ];
    }

    public static function datatypeOptionsAndColors()
    {
        return [
            'options' => [
                Datatype::Array->value => __('models.commons.datatype.array'),
                Datatype::Number->value => __('models.commons.datatype.number'),
                Datatype::String->value => __('models.commons.datatype.string'),
            ],
            'colors' => [
                Datatype::Array->value => 'warning',
                Datatype::Number->value => 'success',
                Datatype::String->value => 'info',
            ],
        ];
    }

    public static function phaseDescriptions()
    {
        return [
            Phase::One->value => __('forms.commons.phase.1'),
            Phase::Two->value => __('forms.commons.phase.2'),
            Phase::Three->value => __('forms.commons.phase.3'),
            Phase::Four->value => __('forms.commons.phase.4'),
            Phase::Five->value => __('forms.commons.phase.5'),
            Phase::Six->value => __('forms.commons.phase.6'),
        ];
    }

    public static function typeDescriptions()
    {
        return [
            Type::Getter->value => __('forms.commons.type.getter'),
            Type::Full->value => __('forms.commons.type.full'),
            Type::Header->value => __('forms.commons.type.header'),
            Type::Meta->value => __('forms.commons.type.meta'),
            Type::Query->value => __('forms.commons.type.query'),
            Type::Body->value => __('forms.commons.type.body'),
            Type::File->value => __('forms.commons.type.file'),
        ];
    }

    public static function datatypeDescriptions()
    {
        return [
            null => __('forms.engine.descriptions.input_datatype'),
            Datatype::Array->value => __('forms.commons.datatype.array'),
            Datatype::Number->value => __('forms.commons.datatype.number'),
            Datatype::String->value => __('forms.commons.datatype.string'),
        ];
    }
}
