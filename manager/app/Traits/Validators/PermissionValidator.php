<?php

namespace App\Traits\Validators;

use App\Rules\Permission\ActionField;
use App\Services\Security;
use Illuminate\Validation\Rule;

trait PermissionValidator
{
    private static function validateName($constraint = 'required', $ignore = null)
    {
        $unique = Rule::unique('permissions', 'name');

        if ($ignore) {
            $unique->ignore($ignore);
        }

        return [$constraint, 'string', 'max:255', $unique];
    }

    private static function validateAppliedFor($constraint = 'required')
    {
        return [$constraint, 'string', Rule::in(array_keys(Security::generatePermissionList(true)))];
    }

    private static function validateAction($constraint = 'required')
    {
        return [$constraint, 'string', new ActionField];
    }

    public static function validatePermission($ignore = null)
    {
        return [
            'name' => self::validateName(ignore: $ignore),
            'applied_for' => self::validateAppliedFor(),
            'action' => self::validateAction(),
            'description' => ['nullable'],
        ];
    }
}
