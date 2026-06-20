<?php

namespace App\Traits\Validators;

use App\Enums\Datatype;
use App\Enums\Engine\Hash;
use App\Enums\Engine\Type;
use App\Rules\Engine\TypeField;
use Illuminate\Validation\Rule;

trait EngineValidator
{
    private static function validateName($constraint = 'required', $ignore = null)
    {
        $unique = Rule::unique('engines', 'name');

        if ($ignore) {
            $unique->ignore($ignore);
        }

        return [$constraint, 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $unique];
    }

    private static function validateInputDatatype($constraint = 'required')
    {
        return [$constraint, Rule::enum(Datatype::class)];
    }

    private static function validateType($constraint = 'required')
    {
        return [$constraint, Rule::enum(Type::class), new TypeField];
    }

    private static function validatePosition($constraint = 'required_if:type,indexOf')
    {
        return [$constraint, 'nullable', 'integer'];
    }

    private static function validateDigit($constraint = 'required_if:type,addition,subtraction,multiplication,division,powerOf,remainder')
    {
        return [$constraint, 'nullable', 'numeric'];
    }

    private static function validateHashMethod($constraint = 'required_if:type,hash')
    {
        return [$constraint, 'nullable', Rule::enum(Hash::class)];
    }

    private static function validateSeparator($constraint = 'nullable')
    {
        return [$constraint, 'nullable', 'string', 'max:255'];
    }

    private static function validateOutputDatatype($constraint = 'required')
    {
        return [$constraint, Rule::enum(Datatype::class)];
    }

    public static function validateEngine($ignore = null)
    {
        return [
            'name' => self::validateName(ignore: $ignore),
            'input_datatype' => self::validateInputDatatype(),
            'type' => self::validateType(),
            'position' => self::validatePosition(),
            'digit' => self::validateDigit(),
            'hash_method' => self::validateHashMethod(),
            'separator' => self::validateSeparator(),
            'output_datatype' => self::validateOutputDatatype(),
            'description' => ['nullable'],
        ];
    }
}
