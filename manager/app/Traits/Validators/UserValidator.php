<?php

namespace App\Traits\Validators;

use App\Rules\User\RootField;
use Illuminate\Validation\Rule;

trait UserValidator
{
    private static function validateName($constraint = 'required')
    {
        return [
            $constraint,
            'string',
            'max:255',
        ];
    }

    private static function validateEmail($constraint = 'required', $ignore = null)
    {
        $unique = Rule::unique('users', 'email');

        if ($ignore) {
            $unique->ignore($ignore);
        }

        return [
            $constraint,
            'string',
            'email',
            'max:255',
            $unique,
        ];
    }

    private static function validatePassword($constraint = 'required')
    {
        return [
            $constraint,
            'string',
            'min:4',
            'max:255',
        ];
    }

    private static function validateIsActivated($constraint = 'required')
    {
        return [
            $constraint,
            'boolean',
        ];
    }

    private static function validateIsRoot($constraint = 'required')
    {
        return [
            $constraint,
            'boolean',
            new RootField,
        ];
    }

    private static function validateIsVerified($constraint = 'required')
    {
        return [
            $constraint,
            'boolean',
        ];
    }

    public static function validateUser($ignore = null, $passwordConstraint = 'required')
    {
        return [
            'name' => self::validateName(),
            'email' => self::validateEmail(ignore: $ignore),
            'password' => self::validatePassword($passwordConstraint),
            'is_activated' => self::validateIsActivated(),
            'is_root' => self::validateIsRoot(),
            'is_verified' => self::validateIsVerified(),
        ];
    }
}
