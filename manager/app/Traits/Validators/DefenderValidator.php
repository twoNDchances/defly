<?php

namespace App\Traits\Validators;

use App\Enums\Defender\DeploymentStatus;
use App\Enums\Defender\Status;
use Illuminate\Validation\Rule;

trait DefenderValidator
{
    private static function validateName($constraint = 'required', $ignore = null)
    {
        $unique = Rule::unique('defenders', 'name');

        if ($ignore) {
            $unique->ignore($ignore);
        }

        return [$constraint, 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $unique];
    }

    private static function validateProxyPort($constraint = 'required')
    {
        return [$constraint, 'integer', 'min:1', 'max:65535'];
    }

    private static function validateEnvironmentVariables($constraint = 'required')
    {
        return [$constraint, 'array'];
    }

    public static function validateDefender($ignore = null)
    {
        return [
            'name' => self::validateName(ignore: $ignore),
            'proxy_port' => self::validateProxyPort(),
            'environment_variables' => self::validateEnvironmentVariables(),
            'description' => ['nullable'],
        ];
    }
}
