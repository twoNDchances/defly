<?php

namespace App\Traits\Filament\Specifics;

use App\Enums\Datatype;
use App\Enums\Method;
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

    public static function methodOptionsAndColors()
    {
        return [
            'options' => [
                Method::Get->value => 'GET',
                Method::Post->value => 'POST',
                Method::Put->value => 'PUT',
                Method::Patch->value => 'PATCH',
                Method::Delete->value => 'DELETE',
            ],
            'colors' => [
                Method::Get->value => 'success',
                Method::Post->value => 'warning',
                Method::Put->value => 'info',
                Method::Patch->value => 'purple',
                Method::Delete->value => 'danger',
            ],
        ];
    }

    public static function phaseDescriptions()
    {
        return [
            Phase::One->value => __('forms.generals.specials.phase.1'),
            Phase::Two->value => __('forms.generals.specials.phase.2'),
            Phase::Three->value => __('forms.generals.specials.phase.3'),
            Phase::Four->value => __('forms.generals.specials.phase.4'),
            Phase::Five->value => __('forms.generals.specials.phase.5'),
            Phase::Six->value => __('forms.generals.specials.phase.6'),
        ];
    }

    public static function typeDescriptions()
    {
        return [
            Type::Getter->value => __('forms.generals.specials.type.getter'),
            Type::Full->value => __('forms.generals.specials.type.full'),
            Type::Header->value => __('forms.generals.specials.type.header'),
            Type::Meta->value => __('forms.generals.specials.type.meta'),
            Type::Query->value => __('forms.generals.specials.type.query'),
            Type::Body->value => __('forms.generals.specials.type.body'),
            Type::File->value => __('forms.generals.specials.type.file'),
        ];
    }

    public static function datatypeDescriptions()
    {
        return [
            null => __('forms.engine.descriptions.input_datatype'),
            Datatype::Array->value => __('forms.generals.specials.datatype.array'),
            Datatype::Number->value => __('forms.generals.specials.datatype.number'),
            Datatype::String->value => __('forms.generals.specials.datatype.string'),
        ];
    }

    public static function methodDescriptions()
    {
        return [
            Method::Get->value => __('forms.generals.specials.method.get'),
            Method::Post->value => __('forms.generals.specials.method.post'),
            Method::Put->value => __('forms.generals.specials.method.put'),
            Method::Patch->value => __('forms.generals.specials.method.patch'),
            Method::Delete->value => __('forms.generals.specials.method.delete'),
        ];
    }
}
