<?php

namespace App\Traits\Validators;

use App\Enums\Phase;
use App\Enums\Rule\Comparator;
use App\Rules\Rule\ComparatorField;
use App\Rules\Rule\TargetField;
use Illuminate\Validation\Rule;

trait RuleValidator
{
    private static function validateName($constraint = 'required', $ignore = null)
    {
        $unique = Rule::unique('rules', 'name');

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

    private static function validatePhase($constraint = 'required')
    {
        return [
            $constraint,
            Rule::enum(Phase::class),
        ];
    }

    private static function validateTarget($phase = null, $constraint = 'required')
    {
        return [
            $constraint,
            Rule::exists('targets', 'id'),
            new TargetField($phase === null ? null : (int) $phase),
        ];
    }

    private static function validateComparator($constraint = 'required')
    {
        return [
            $constraint,
            Rule::enum(Comparator::class),
            new ComparatorField,
        ];
    }

    private static function validateIsInversed($constraint = 'required')
    {
        return [
            $constraint,
            'boolean',
        ];
    }

    private static function validateStringValue($constraint = 'required_if:comparator,@contains,@match,@mirror,@startsWith,@endsWith,@regExp')
    {
        return [
            $constraint,
            'nullable',
            'string',
        ];
    }

    private static function validateNumberValue($constraint = 'required_if:comparator,@equal,@greaterThan,@lessThan,@greaterThanOrEqual,@lessThanOrEqual')
    {
        return [
            $constraint,
            'nullable',
            'numeric',
        ];
    }

    private static function validateNumberFromValue($constraint = 'required_if:comparator,@inRange')
    {
        return [
            $constraint,
            'nullable',
            'numeric',
            'lt:number_to_value',
        ];
    }

    private static function validateNumberToValue($constraint = 'required_if:comparator,@inRange')
    {
        return [
            $constraint,
            'nullable',
            'numeric',
            'gt:number_from_value',
        ];
    }

    private static function validateWordlist($constraint = 'required_if:comparator,@similar,@search,@check,@checkRegExp')
    {
        return [
            $constraint,
            'nullable',
            Rule::exists('wordlists', 'id'),
        ];
    }

    public static function validateRule($ignore = null)
    {
        return [
            'name' => self::validateName(ignore: $ignore),
            'phase' => self::validatePhase(),
            'target_id' => self::validateTarget(),
            'comparator' => self::validateComparator(),
            'is_inversed' => self::validateIsInversed(),
            'string_value' => self::validateStringValue(),
            'number_value' => self::validateNumberValue(),
            'number_from_value' => self::validateNumberFromValue(),
            'number_to_value' => self::validateNumberToValue(),
            'wordlist_id' => self::validateWordlist(),
            'description' => ['nullable'],
        ];
    }
}
