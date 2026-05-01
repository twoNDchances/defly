<?php

namespace App\Traits\Validators;

use App\Enums\Wordlist\Type;
use Illuminate\Validation\Rule;

trait WordlistValidator
{
    private static function validateName($constraint = 'required', $ignore = null)
    {
        $unique = Rule::unique('wordlists', 'name');

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

    private static function validateType($constraint = 'required')
    {
        return [
            $constraint,
            Rule::enum(Type::class),
        ];
    }

    private static function validateWordFile($constraint = 'required_if:type,file')
    {
        return [
            $constraint,
            'nullable',
        ];
    }

    private static function validateWordJson($constraint = 'required_if:type,json')
    {
        return [
            $constraint,
            'array',
            'min:1',
        ];
    }

    private static function validateWordJsonWord($constraint = 'required')
    {
        return [
            $constraint,
            'string',
            'max:255',
        ];
    }

    public static function validateWordlist($ignore = null)
    {
        return [
            'name' => self::validateName(ignore: $ignore),
            'type' => self::validateType(),
            'word_file' => self::validateWordFile(),
            'word_json' => self::validateWordJson(),
            'word_json.*.word' => self::validateWordJsonWord(),
            'description' => ['nullable'],
        ];
    }
}
