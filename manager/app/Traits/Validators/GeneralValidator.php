<?php

namespace App\Traits\Validators;

use Illuminate\Validation\Rule;

trait GeneralValidator
{
    private static function validateDescription()
    {
        return [
            'nullable',
        ];
    }

    private static function validateLabels()
    {
        return [
            Rule::exists('labels', 'id'),
        ];
    }
}
