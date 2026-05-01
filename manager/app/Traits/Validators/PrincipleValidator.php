<?php

namespace App\Traits\Validators;

use App\Enums\Phase;
use App\Enums\Principle\ValidationStatus;
use Illuminate\Validation\Rule;

trait PrincipleValidator
{
    private static function validateName($constraint = 'required', $ignore = null)
    {
        $unique = Rule::unique('principles', 'name');

        if ($ignore) {
            $unique->ignore($ignore);
        }

        return [$constraint, 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $unique];
    }

    private static function validateLevel($constraint = 'required')
    {
        return [$constraint, 'integer', 'min:1'];
    }

    private static function validatePhase($constraint = 'required')
    {
        return [$constraint, Rule::enum(Phase::class)];
    }

    private static function validateValidationStatus($constraint = 'nullable')
    {
        return [$constraint, Rule::enum(ValidationStatus::class)];
    }

    private static function validateValidationDetails($constraint = 'nullable')
    {
        return [$constraint, 'array'];
    }

    public static function validatePrinciple($ignore = null)
    {
        return [
            'name' => self::validateName(ignore: $ignore),
            'level' => self::validateLevel(),
            'phase' => self::validatePhase(),
            'validation_status' => self::validateValidationStatus(),
            'validation_details' => self::validateValidationDetails(),
            'description' => ['nullable'],
        ];
    }
}
