<?php

namespace App\Traits\Validators;

use App\Enums\Datatype;
use App\Enums\Phase;
use App\Enums\Type;
use Illuminate\Validation\Rule;

trait PatternValidator
{
    private static function validateName($constraint = 'required', $ignore = null)
    {
        $unique = Rule::unique('patterns', 'name');

        if ($ignore) {
            $unique->ignore($ignore);
        }

        return [$constraint, 'string', 'max:255', $unique];
    }

    private static function validatePhase($constraint = 'required')
    {
        return [$constraint, Rule::enum(Phase::class)];
    }

    private static function validateType($constraint = 'required')
    {
        return [$constraint, Rule::enum(Type::class), Rule::notIn([Type::Getter->value])];
    }

    private static function validateDatatype($constraint = 'required')
    {
        return [$constraint, Rule::enum(Datatype::class)];
    }

    private static function validateTargets($constraint = 'nullable')
    {
        return [$constraint, 'array'];
    }

    private static function validateTargetItem($constraint = 'nullable')
    {
        return [$constraint, Rule::exists('targets', 'id')];
    }

    public static function validatePattern($ignore = null)
    {
        return [
            'name' => self::validateName(ignore: $ignore),
            'phase' => self::validatePhase(),
            'type' => self::validateType(),
            'datatype' => self::validateDatatype(),
            'targets' => self::validateTargets(),
            'targets.*' => self::validateTargetItem(),
            'description' => ['nullable'],
        ];
    }
}
