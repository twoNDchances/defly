<?php

namespace App\Traits\Validators;

use Illuminate\Validation\Rule;

trait GuardValidator
{
    private static function validateName($constraint = 'required', $ignore = null)
    {
        $unique = Rule::unique('guards', 'name');

        if ($ignore) {
            $unique->ignore($ignore);
        }

        return [$constraint, 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $unique];
    }

    private static function validateExpiredAt($constraint = 'nullable')
    {
        return [$constraint, 'date'];
    }

    public static function validateGuard($ignore = null)
    {
        return [
            'name' => self::validateName(ignore: $ignore),
            'description' => ['nullable'],
            'expired_at' => self::validateExpiredAt(),
        ];
    }
}
