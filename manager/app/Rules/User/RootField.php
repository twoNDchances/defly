<?php

namespace App\Rules\User;

use App\Services\Identification;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class RootField implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! Identification::isRoot()) {
            $fail("The {$attribute} can only be used by authorized users.");
        }
    }
}
