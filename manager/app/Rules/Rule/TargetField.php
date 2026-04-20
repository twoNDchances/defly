<?php

namespace App\Rules\Rule;

use App\Models\Target;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class TargetField implements ValidationRule
{
    public $phase;

    public function __construct(int $phase)
    {
        $this->phase = $phase;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $target = Target::find($value);
        if (! $target) {
            $fail("$attribute not exists.");
        }
        if ($target->phase->value != $this->phase) {
            $fail("$attribute phase miss match with phase $this->phase.");
        }
    }
}
