<?php

namespace App\Traits\Filament\Specifics\Rule;

use App\Enums\Datatype;
use App\Enums\Rule\Comparator;
use App\Traits\Filament\Specifics\GeneralData;

trait RuleData
{
    use GeneralData;

    public static function comparatorOptionsPerDatatype()
    {
        return [
            Datatype::Array->value => [
                Comparator::Similar->value => __('models.rule.extras.comparator.@similar'),
                Comparator::Contains->value => __('models.rule.extras.comparator.@contains'),
                Comparator::Match->value => __('models.rule.extras.comparator.@match'),
                Comparator::Search->value => __('models.rule.extras.comparator.@search'),
            ],
            Datatype::Number->value => [
                Comparator::Equal->value => __('models.rule.extras.comparator.@equal'),
                Comparator::GreaterThan->value => __('models.rule.extras.comparator.@greaterThan'),
                Comparator::LessThan->value => __('models.rule.extras.comparator.@lessThan'),
                Comparator::GreaterThanOrEqual->value => __('models.rule.extras.comparator.@greaterThanOrEqual'),
                Comparator::LessThanOrEqual->value => __('models.rule.extras.comparator.@lessThanOrEqual'),
                Comparator::InRange->value => __('models.rule.extras.comparator.@inRange'),
            ],
            Datatype::String->value => [
                Comparator::Mirror->value => __('models.rule.extras.comparator.@mirror'),
                Comparator::StartsWith->value => __('models.rule.extras.comparator.@startsWith'),
                Comparator::EndsWith->value => __('models.rule.extras.comparator.@endsWith'),
                Comparator::Check->value => __('models.rule.extras.comparator.@check'),
                Comparator::RegExp->value => __('models.rule.extras.comparator.@regExp'),
                Comparator::CheckRegExp->value => __('models.rule.extras.comparator.@checkRegExp'),
            ],
        ];
    }

    public static function comparatorOptions()
    {
        return [
            Comparator::Similar->value => __('models.rule.extras.comparator.@similar'),
            Comparator::Contains->value => __('models.rule.extras.comparator.@contains'),
            Comparator::Match->value => __('models.rule.extras.comparator.@match'),
            Comparator::Search->value => __('models.rule.extras.comparator.@search'),
            Comparator::Equal->value => __('models.rule.extras.comparator.@equal'),
            Comparator::GreaterThan->value => __('models.rule.extras.comparator.@greaterThan'),
            Comparator::LessThan->value => __('models.rule.extras.comparator.@lessThan'),
            Comparator::GreaterThanOrEqual->value => __('models.rule.extras.comparator.@greaterThanOrEqual'),
            Comparator::LessThanOrEqual->value => __('models.rule.extras.comparator.@lessThanOrEqual'),
            Comparator::InRange->value => __('models.rule.extras.comparator.@inRange'),
            Comparator::Mirror->value => __('models.rule.extras.comparator.@mirror'),
            Comparator::StartsWith->value => __('models.rule.extras.comparator.@startsWith'),
            Comparator::EndsWith->value => __('models.rule.extras.comparator.@endsWith'),
            Comparator::Check->value => __('models.rule.extras.comparator.@check'),
            Comparator::RegExp->value => __('models.rule.extras.comparator.@regExp'),
            Comparator::CheckRegExp->value => __('models.rule.extras.comparator.@checkRegExp'),
        ];
    }

    public static function comparatorDescriptions()
    {
        return [
            null => __('forms.rule.descriptions.comparator'),
            Comparator::Similar->value => __('forms.rule.extras.comparator.@similar'),
            Comparator::Contains->value => __('forms.rule.extras.comparator.@contains'),
            Comparator::Match->value => __('forms.rule.extras.comparator.@match'),
            Comparator::Search->value => __('forms.rule.extras.comparator.@search'),
            Comparator::Equal->value => __('forms.rule.extras.comparator.@equal'),
            Comparator::GreaterThan->value => __('forms.rule.extras.comparator.@greaterThan'),
            Comparator::LessThan->value => __('forms.rule.extras.comparator.@lessThan'),
            Comparator::GreaterThanOrEqual->value => __('forms.rule.extras.comparator.@greaterThanOrEqual'),
            Comparator::LessThanOrEqual->value => __('forms.rule.extras.comparator.@lessThanOrEqual'),
            Comparator::InRange->value => __('forms.rule.extras.comparator.@inRange'),
            Comparator::Mirror->value => __('forms.rule.extras.comparator.@mirror'),
            Comparator::StartsWith->value => __('forms.rule.extras.comparator.@startsWith'),
            Comparator::EndsWith->value => __('forms.rule.extras.comparator.@endsWith'),
            Comparator::Check->value => __('forms.rule.extras.comparator.@check'),
            Comparator::RegExp->value => __('forms.rule.extras.comparator.@regExp'),
            Comparator::CheckRegExp->value => __('forms.rule.extras.comparator.@checkRegExp'),
        ];
    }

    public static function saveForm($data)
    {
        $data['configurations'] = match ($data['comparator']) {
            Comparator::Equal->value,
            Comparator::GreaterThan->value,
            Comparator::LessThan->value,
            Comparator::GreaterThanOrEqual->value,
            Comparator::LessThanOrEqual->value => [
                'number' => $data['number_value'],
            ],
            Comparator::InRange->value => [
                'number_from' => $data['number_from_value'],
                'number_to' => $data['number_to_value'],
            ],
            Comparator::Contains->value,
            Comparator::Match->value,
            Comparator::Mirror->value,
            Comparator::StartsWith->value,
            Comparator::EndsWith->value,
            Comparator::RegExp->value => [
                'string' => $data['string_value'],
            ],
            default => null,
        };

        return $data;
    }

    public static function loadForm($data)
    {
        $configurations = $data['configurations'];
        switch ($data['comparator']) {
            case Comparator::Equal->value:
            case Comparator::GreaterThan->value:
            case Comparator::LessThan->value:
            case Comparator::GreaterThanOrEqual->value:
            case Comparator::LessThanOrEqual->value:
                $data['number_value'] = $configurations['number'];
                break;
            case Comparator::InRange->value:
                $data['number_from_value'] = $configurations['number_from'];
                $data['number_to_value'] = $configurations['number_to'];
                break;
            case Comparator::Contains->value:
            case Comparator::Match->value:
            case Comparator::Mirror->value:
            case Comparator::StartsWith->value:
            case Comparator::EndsWith->value:
            case Comparator::RegExp->value:
                $data['string_value'] = $configurations['string'];
            default:
                break;
        }

        return $data;
    }
}
