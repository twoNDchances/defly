<?php

namespace App\Traits\Validators;

use Illuminate\Validation\Rule;

trait LabelValidator
{
    private static function validateName($constraint = 'required', $ignore = null)
    {
        $unique = Rule::unique('labels', 'name');

        if ($ignore) {
            $unique->ignore($ignore);
        }

        return [$constraint, 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $unique];
    }

    private static function validateColor($constraint = 'required')
    {
        return [$constraint, 'hex_color'];
    }

    public static function validateLabel($ignore = null)
    {
        return [
            'name' => self::validateName(ignore: $ignore),
            'color' => self::validateColor(),
            'description' => ['nullable'],
        ];
    }
}
