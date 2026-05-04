<?php

namespace App\Rules\Engine;

use App\Enums\Datatype;
use App\Enums\Engine\Type;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class TypeField implements DataAwareRule, ValidationRule
{
    protected array $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $inputDatatype = $this->data['input_datatype'] ?? null;

        if (! is_string($inputDatatype) || ! is_string($value)) {
            return;
        }

        $allowed = self::typesPerDatatype()[$inputDatatype] ?? null;

        if ($allowed === null) {
            return;
        }

        if (! in_array($value, $allowed, true)) {
            $fail("The {$attribute} is invalid for the selected input datatype.");
        }
    }

    private static function typesPerDatatype(): array
    {
        return [
            Datatype::Array->value => [
                Type::IndexOf->value,
                Type::Merge->value,
            ],
            Datatype::Number->value => [
                Type::Addition->value,
                Type::Subtraction->value,
                Type::Multiplication->value,
                Type::Division->value,
                Type::PowerOf->value,
                Type::Remainder->value,
                Type::ToString->value,
            ],
            Datatype::String->value => [
                Type::Lower->value,
                Type::Upper->value,
                Type::Capitalize->value,
                Type::Trim->value,
                Type::TrimLeft->value,
                Type::TrimRight->value,
                Type::RemoveWhitespace->value,
                Type::Length->value,
                Type::Hash->value,
                Type::Split->value,
            ],
        ];
    }
}
