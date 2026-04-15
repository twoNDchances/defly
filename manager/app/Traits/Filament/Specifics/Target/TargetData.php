<?php

namespace App\Traits\Filament\Specifics\Target;

use App\Enums\Phase;
use App\Enums\Type;
use App\Traits\Filament\Specifics\GeneralData;

trait TargetData
{
    use GeneralData;

    public static function typeOptionsAndColorsPerPhases()
    {
        return [
            Phase::One->value => [
                'options' => [
                    Type::Getter->value => __('models.generals.specials.type.getter'),
                    Type::Full->value => __('models.generals.specials.type.full'),
                ],
                'colors' => [
                    Type::Getter->value => 'purple',
                    Type::Full->value => 'gray',
                ],
            ],
            Phase::Two->value => [
                'options' => [
                    Type::Getter->value => __('models.generals.specials.type.getter'),
                    Type::Full->value => __('models.generals.specials.type.full'),
                    Type::Header->value => __('models.generals.specials.type.header'),
                    Type::Meta->value => __('models.generals.specials.type.meta'),
                    Type::Query->value => __('models.generals.specials.type.query'),
                ],
                'colors' => [
                    Type::Getter->value => 'purple',
                    Type::Full->value => 'gray',
                    Type::Header->value => 'info',
                    Type::Meta->value => 'danger',
                    Type::Query->value => 'warning',
                ],
            ],
            Phase::Three->value => [
                'options' => [
                    Type::Getter->value => __('models.generals.specials.type.getter'),
                    Type::Full->value => __('models.generals.specials.type.full'),
                    Type::Body->value => __('models.generals.specials.type.body'),
                    Type::File->value => __('models.generals.specials.type.file'),
                ],
                'colors' => [
                    Type::Getter->value => 'purple',
                    Type::Full->value => 'gray',
                    Type::Body->value => 'sky',
                    Type::File->value => 'teal',
                ],
            ],
            Phase::Four->value => [
                'options' => [
                    Type::Getter->value => __('models.generals.specials.type.getter'),
                    Type::Full->value => __('models.generals.specials.type.full'),
                    Type::Header->value => __('models.generals.specials.type.header'),
                    Type::Meta->value => __('models.generals.specials.type.meta'),
                ],
                'colors' => [
                    Type::Getter->value => 'purple',
                    Type::Full->value => 'gray',
                    Type::Header->value => 'info',
                    Type::Meta->value => 'danger',
                ],
            ],
            Phase::Five->value => [
                'options' => [
                    Type::Getter->value => __('models.generals.specials.type.getter'),
                    Type::Full->value => __('models.generals.specials.type.full'),
                    Type::Body->value => __('models.generals.specials.type.body'),
                ],
                'colors' => [
                    Type::Getter->value => 'purple',
                    Type::Full->value => 'gray',
                    Type::Body->value => 'sky',
                ],
            ],
            Phase::Six->value => [
                'options' => [
                    Type::Getter->value => __('models.generals.specials.type.getter'),
                    Type::Full->value => __('models.generals.specials.type.full'),
                ],
                'colors' => [
                    Type::Getter->value => 'purple',
                    Type::Full->value => 'gray',
                ],
            ],
        ];
    }
}
