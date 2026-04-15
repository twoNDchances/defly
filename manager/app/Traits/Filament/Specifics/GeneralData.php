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
                Phase::One->value => __('models.generals.specials.phase.1'),
                Phase::Two->value => __('models.generals.specials.phase.2'),
                Phase::Three->value => __('models.generals.specials.phase.3'),
                Phase::Four->value => __('models.generals.specials.phase.4'),
                Phase::Five->value => __('models.generals.specials.phase.5'),
                Phase::Six->value => __('models.generals.specials.phase.6'),
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
                Type::Getter->value => __('models.generals.specials.type.getter'),
                Type::Full->value => __('models.generals.specials.type.full'),
                Type::Header->value => __('models.generals.specials.type.header'),
                Type::Meta->value => __('models.generals.specials.type.meta'),
                Type::Query->value => __('models.generals.specials.type.query'),
                Type::Body->value => __('models.generals.specials.type.body'),
                Type::File->value => __('models.generals.specials.type.file'),
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
                Datatype::Array->value => __('models.generals.specials.datatype.array'),
                Datatype::Number->value => __('models.generals.specials.datatype.number'),
                Datatype::String->value => __('models.generals.specials.datatype.string'),
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
            Phase::One->value => __('forms.generals.specifics.phase.1'),
            Phase::Two->value => __('forms.generals.specifics.phase.2'),
            Phase::Three->value => __('forms.generals.specifics.phase.3'),
            Phase::Four->value => __('forms.generals.specifics.phase.4'),
            Phase::Five->value => __('forms.generals.specifics.phase.5'),
            Phase::Six->value => __('forms.generals.specifics.phase.6'),
        ];
    }

    public static function typeDescriptions()
    {
        return [
            Type::Getter->value => __('forms.generals.specifics.type.getter'),
            Type::Full->value => __('forms.generals.specifics.type.full'),
            Type::Header->value => __('forms.generals.specifics.type.header'),
            Type::Meta->value => __('forms.generals.specifics.type.meta'),
            Type::Query->value => __('forms.generals.specifics.type.query'),
            Type::Body->value => __('forms.generals.specifics.type.body'),
            Type::File->value => __('forms.generals.specifics.type.file'),
        ];
    }

    public static function datatypeDescriptions()
    {
        return [
            null => __('forms.engine.descriptions.input_datatype'),
            Datatype::Array->value => __('forms.generals.specifics.datatype.array'),
            Datatype::Number->value => __('forms.generals.specifics.datatype.number'),
            Datatype::String->value => __('forms.generals.specifics.datatype.string'),
        ];
    }
}
