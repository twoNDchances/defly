<?php

namespace App\Traits\Validators;

use App\Enums\Datatype;
use App\Enums\Phase;
use App\Enums\Type;
use App\Rules\KebabNameField;
use Illuminate\Validation\Rule;

trait TargetValidator
{
    use GeneralValidator;

    private static function validatePhase($constraint = 'required')
    {
        return [
            $constraint,
            Rule::enum(Phase::class),
        ];
    }

    private static function validateType($constraint = 'required')
    {
        return [
            $constraint,
            Rule::enum(Type::class),
        ];
    }

    private static function validatePattern($constraint = 'required_if:type,full')
    {
        return [
            $constraint,
            Rule::exists('patterns', 'id'),
        ];
    }

    private static function validateName($constraint = 'required')
    {
        return [
            $constraint,
            new KebabNameField(),
            Rule::unique('targets', 'name'),
        ];
    }

    private static function validateDatatype($constraint = 'required')
    {
        return [
            $constraint,
            Rule::enum(Datatype::class),
        ];
    }

    private static function validateWordlist($constraint = 'required')
    {
        return [
            $constraint,
            Rule::exists('wordlist', 'id'),
        ];
    }
}
