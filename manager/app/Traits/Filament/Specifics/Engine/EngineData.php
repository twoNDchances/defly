<?php

namespace App\Traits\Filament\Specifics\Engine;

use App\Enums\Engine\Datatype;
use App\Enums\Engine\Type;

trait EngineData
{
    public static function datatypeOptionsAndColors()
    {
        return [
            'options' => [
                Datatype::Array->value => __('models.engine.extras.datatype.array'),
                Datatype::Number->value => __('models.engine.extras.datatype.number'),
                Datatype::String->value => __('models.engine.extras.datatype.string'),
            ],
            'colors' => [
                Datatype::Array->value => 'warning',
                Datatype::Number->value => 'success',
                Datatype::String->value => 'info',
            ],
        ];
    }

    public static function datatypeDescriptions()
    {
        return [
            null => __('forms.engine.descriptions.input_datatype'),
            Datatype::Array->value => __('forms.engine.extras.datatype.array'),
            Datatype::Number->value => __('forms.engine.extras.datatype.number'),
            Datatype::String->value => __('forms.engine.extras.datatype.string'),
        ];
    }

    public static function typeDescriptionsPerDatatypes()
    {
        return [
            Datatype::Array->value => [
                Type::IndexOf->value => __('forms.engine.extras.type.indexOf'),
                Type::Merge->value => __('forms.engine.extras.type.merge'),
            ],
            Datatype::Number->value => [
                Type::Addition->value => __('forms.engine.extras.type.addition'),
                Type::Subtraction->value => __('forms.engine.extras.type.subtraction'),
                Type::Multiplication->value => __('forms.engine.extras.type.multiplication'),
                Type::Division->value => __('forms.engine.extras.type.division'),
                Type::PowerOf->value => __('forms.engine.extras.type.powerOf'),
                Type::Remainder->value => __('forms.engine.extras.type.remainder'),
                Type::ToString->value => __('forms.engine.extras.type.toString'),
            ],
            Datatype::String->value => [
                Type::Lower->value => __('forms.engine.extras.type.lower'),
                Type::Upper->value => __('forms.engine.extras.type.upper'),
                Type::Capitalize->value => __('forms.engine.extras.type.capitalize'),
                Type::Trim->value => __('forms.engine.extras.type.trim'),
                Type::TrimLeft->value => __('forms.engine.extras.type.trimLeft'),
                Type::TrimRight->value => __('forms.engine.extras.type.trimRight'),
                Type::RemoveWhitespace->value => __('forms.engine.extras.type.removeWhitespace'),
                Type::Length->value => __('forms.engine.extras.type.length'),
                Type::Hash->value => __('forms.engine.extras.type.hash'),
                Type::Split->value => __('forms.engine.extras.type.split'),
            ],
        ];
    }

    public static function typeOptionsPerDatatypes()
    {
        return [
            Datatype::Array->value => [
                Type::IndexOf->value => __('models.engine.extras.type.indexOf'),
                Type::Merge->value => __('models.engine.extras.type.merge'),
            ],
            Datatype::Number->value => [
                Type::Addition->value => __('models.engine.extras.type.addition'),
                Type::Subtraction->value => __('models.engine.extras.type.subtraction'),
                Type::Multiplication->value => __('models.engine.extras.type.multiplication'),
                Type::Division->value => __('models.engine.extras.type.division'),
                Type::PowerOf->value => __('models.engine.extras.type.powerOf'),
                Type::Remainder->value => __('models.engine.extras.type.remainder'),
                Type::ToString->value => __('models.engine.extras.type.toString'),
            ],
            Datatype::String->value => [
                Type::Lower->value => __('models.engine.extras.type.lower'),
                Type::Upper->value => __('models.engine.extras.type.upper'),
                Type::Capitalize->value => __('models.engine.extras.type.capitalize'),
                Type::Trim->value => __('models.engine.extras.type.trim'),
                Type::TrimLeft->value => __('models.engine.extras.type.trimLeft'),
                Type::TrimRight->value => __('models.engine.extras.type.trimRight'),
                Type::RemoveWhitespace->value => __('models.engine.extras.type.removeWhitespace'),
                Type::Length->value => __('models.engine.extras.type.length'),
                Type::Hash->value => __('models.engine.extras.type.hash'),
                Type::Split->value => __('models.engine.extras.type.split'),
            ],
        ];
    }

    public static function saveForm($data)
    {
        $data['configurations'] = match ($data['type']) {
            Type::IndexOf->value => ['position' => $data['position']],

            Type::Merge->value,
            Type::Split->value => ['separator' => $data['separator']],

            Type::Addition->value,
            Type::Subtraction->value,
            Type::Multiplication->value,
            Type::Division->value,
            Type::PowerOf->value,
            Type::Remainder->value => ['digit' => $data['digit']],

            Type::Hash->value => ['hash_method' => $data['hash_method']],

            default => null,
        };
        $data['output_datatype'] = match ($data['type']) {
            Type::Split->value => Datatype::Array->value,

            Type::Addition->value,
            Type::Subtraction->value,
            Type::Multiplication->value,
            Type::Division->value,
            Type::PowerOf->value,
            Type::Remainder->value,
            Type::Length->value => Datatype::Number->value,

            Type::IndexOf->value,
            Type::Lower->value,
            Type::Upper->value,
            Type::Capitalize->value,
            Type::Trim->value,
            Type::TrimLeft->value,
            Type::TrimRight->value,
            Type::RemoveWhitespace->value,
            Type::Hash->value,
            Type::Merge->value,
            Type::ToString->value => Datatype::String->value,
        };

        return $data;
    }

    public static function loadForm($data)
    {
        $configurations = $data['configurations'];
        switch ($data['type']) {
            case Type::IndexOf->value:
                $data['position'] = $configurations['position'];
                break;
            case Type::Merge->value:
            case Type::Split->value:
                $data['separator'] = $configurations['separator'];
                break;
            case Type::Addition->value:
            case Type::Subtraction->value:
            case Type::Multiplication->value:
            case Type::Division->value:
            case Type::PowerOf->value:
            case Type::Remainder->value:
                $data['digit'] = $configurations['digit'];
                break;
            case Type::Hash->value:
                $data['hash_method'] = $configurations['hash_method'];
                break;
            default:
                break;
        }

        return $data;
    }
}
