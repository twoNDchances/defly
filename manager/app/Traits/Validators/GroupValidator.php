<?php

namespace App\Traits\Validators;

use Illuminate\Validation\Rule;

trait GroupValidator
{
    private static function validateName($constraint = 'required', $ignore = null)
    {
        $unique = Rule::unique('groups', 'name');

        if ($ignore) {
            $unique->ignore($ignore);
        }

        return [$constraint, 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $unique];
    }

    public static function validateGroup($ignore = null)
    {
        return [
            'name' => self::validateName(ignore: $ignore),
            'description' => ['nullable'],
        ];
    }
}
