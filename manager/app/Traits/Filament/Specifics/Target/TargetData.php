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
                    Type::Getter->value => __('models.commons.type.getter'),
                    Type::Full->value => __('models.commons.type.full'),
                ],
                'colors' => [
                    Type::Getter->value => 'purple',
                    Type::Full->value => 'gray',
                ],
            ],
            Phase::Two->value => [
                'options' => [
                    Type::Getter->value => __('models.commons.type.getter'),
                    Type::Full->value => __('models.commons.type.full'),
                    Type::Header->value => __('models.commons.type.header'),
                    Type::Meta->value => __('models.commons.type.meta'),
                    Type::Query->value => __('models.commons.type.query'),
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
                    Type::Getter->value => __('models.commons.type.getter'),
                    Type::Full->value => __('models.commons.type.full'),
                    Type::Body->value => __('models.commons.type.body'),
                    Type::File->value => __('models.commons.type.file'),
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
                    Type::Getter->value => __('models.commons.type.getter'),
                    Type::Full->value => __('models.commons.type.full'),
                    Type::Header->value => __('models.commons.type.header'),
                    Type::Meta->value => __('models.commons.type.meta'),
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
                    Type::Getter->value => __('models.commons.type.getter'),
                    Type::Full->value => __('models.commons.type.full'),
                    Type::Body->value => __('models.commons.type.body'),
                ],
                'colors' => [
                    Type::Getter->value => 'purple',
                    Type::Full->value => 'gray',
                    Type::Body->value => 'sky',
                ],
            ],
            Phase::Six->value => [
                'options' => [
                    Type::Getter->value => __('models.commons.type.getter'),
                    Type::Full->value => __('models.commons.type.full'),
                ],
                'colors' => [
                    Type::Getter->value => 'purple',
                    Type::Full->value => 'gray',
                ],
            ],
        ];
    }
}
