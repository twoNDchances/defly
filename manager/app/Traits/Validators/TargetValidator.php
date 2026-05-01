<?php

namespace App\Traits\Validators;

use App\Enums\Datatype;
use App\Enums\Phase;
use App\Enums\Type;
use Illuminate\Validation\Rule;

trait TargetValidator
{
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

    private static function validatePattern($constraint = 'required_if:type,full,meta')
    {
        return [
            $constraint,
            Rule::exists('patterns', 'id'),
        ];
    }

    private static function validateName($constraint = 'required', $ignore = null)
    {
        $unique = Rule::unique('targets', 'name');

        if ($ignore) {
            $unique->ignore($ignore);
        }

        return [
            $constraint,
            'string',
            'max:255',
            'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            $unique,
        ];
    }

    private static function validateDatatype($constraint = 'required')
    {
        return [
            $constraint,
            Rule::enum(Datatype::class),
        ];
    }

    private static function validateWordlist($constraint = 'exclude_unless:datatype,array|required_without:pattern_id')
    {
        return [
            $constraint,
            Rule::exists('wordlists', 'id'),
        ];
    }

    public static function validateTarget($ignore = null)
    {
        return [
            'phase' => self::validatePhase(),
            'type' => self::validateType(),
            'pattern_id' => self::validatePattern(),
            'name' => self::validateName(ignore: $ignore),
            'datatype' => self::validateDatatype(),
            'wordlist_id' => self::validateWordlist(),
            'description' => ['nullable'],
        ];
    }
}
