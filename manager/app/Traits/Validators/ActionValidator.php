<?php

namespace App\Traits\Validators;

use App\Enums\Action\Type;
use App\Enums\Method;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

trait ActionValidator
{
    private static function validateName($constraint = 'required', $ignore = null)
    {
        $unique = Rule::unique('actions', 'name');

        if ($ignore) {
            $unique->ignore($ignore);
        }

        return [$constraint, 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $unique];
    }

    private static function validateType($constraint = 'required')
    {
        return [$constraint, Rule::enum(Type::class)];
    }

    private static function validateDenyStatus($constraint = 'required_if:type,deny')
    {
        return [$constraint, 'nullable', 'integer', Rule::in(array_keys(Response::$statusTexts))];
    }

    private static function validateDenyContentType($constraint = 'required_if:type,deny')
    {
        return [$constraint, 'nullable', Rule::in(['json', 'html'])];
    }

    private static function validateRequiredString($constraint = 'required')
    {
        return [$constraint, 'nullable', 'string'];
    }

    private static function validateRequiredBoolean($constraint = 'required')
    {
        return [$constraint, 'boolean'];
    }

    private static function validateRequestMethod($constraint = 'required_if:type,request')
    {
        return [$constraint, 'nullable', Rule::enum(Method::class)];
    }

    private static function validateRepeater($constraint = 'nullable')
    {
        return [$constraint, 'array'];
    }

    private static function validateKey($constraint = 'required')
    {
        return [$constraint, 'string', 'max:255'];
    }

    private static function validateSetterDatatype($constraint = 'required')
    {
        return [$constraint, Rule::in(['string', 'number'])];
    }

    private static function validateSetterDirective($constraint = 'required_if:type,setter')
    {
        return [$constraint, 'nullable', Rule::in(['set', 'unset'])];
    }

    private static function validateBehavior($constraint = 'required')
    {
        return [$constraint, Rule::in(['override', 'increase', 'decrease'])];
    }

    private static function validatePositiveNumber($constraint = 'required')
    {
        return [$constraint, 'numeric', 'min:1'];
    }

    public static function validateAction($ignore = null)
    {
        return [
            'name' => self::validateName(ignore: $ignore),
            'type' => self::validateType(),
            'deny_status' => self::validateDenyStatus(),
            'deny_content_type' => self::validateDenyContentType(),
            'deny_body' => self::validateRequiredString('required_if:type,deny'),
            'log_format' => self::validateRequiredString('required_if:type,log'),
            'log_console' => self::validateRequiredBoolean('required_if:type,log'),
            'log_file' => self::validateRequiredBoolean('required_if:type,log'),
            'request_url' => self::validateRequiredString('required_if:type,request'),
            'request_method' => self::validateRequestMethod(),
            'request_headers' => self::validateRepeater(),
            'request_headers.*.key' => self::validateKey(),
            'request_headers.*.value' => self::validateRequiredString(),
            'request_body' => self::validateRequiredString('required_if:type,request'),
            'suspect_severity' => self::validateRequiredString('required_if:type,suspect'),
            'setter_directive' => self::validateSetterDirective(),
            'setter_set' => self::validateRepeater('required_if:setter_directive,set'),
            'setter_set.*.key' => self::validateKey(),
            'setter_set.*.datatype' => self::validateSetterDatatype(),
            'setter_set.*.value' => self::validateRequiredString(),
            'setter_unset' => self::validateRepeater('required_if:setter_directive,unset'),
            'setter_unset.*.key' => self::validateKey(),
            'score_behavior' => self::validateBehavior('required_if:type,score'),
            'score_value' => self::validatePositiveNumber('required_if:type,score'),
            'level_behavior' => self::validateBehavior('required_if:type,level'),
            'level_value' => self::validatePositiveNumber('required_if:type,level'),
            'description' => ['nullable'],
        ];
    }
}
