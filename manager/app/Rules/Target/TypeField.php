<?php

namespace App\Rules\Target;

use App\Enums\Phase;
use App\Enums\Type;
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
        $phase = $this->data['phase'] ?? null;

        if (! is_numeric($phase) || ! is_string($value)) {
            return;
        }

        $allowed = self::typesPerPhase()[(int) $phase] ?? null;

        if ($allowed === null) {
            return;
        }

        if (! in_array($value, $allowed, true)) {
            $fail("The {$attribute} is invalid for the selected phase.");
        }
    }

    private static function typesPerPhase(): array
    {
        return [
            Phase::One->value => [
                Type::Getter->value,
                Type::Full->value,
            ],
            Phase::Two->value => [
                Type::Getter->value,
                Type::Full->value,
                Type::Header->value,
                Type::Meta->value,
                Type::Query->value,
            ],
            Phase::Three->value => [
                Type::Getter->value,
                Type::Full->value,
                Type::Body->value,
                Type::File->value,
            ],
            Phase::Four->value => [
                Type::Getter->value,
                Type::Full->value,
                Type::Header->value,
                Type::Meta->value,
            ],
            Phase::Five->value => [
                Type::Getter->value,
                Type::Full->value,
                Type::Body->value,
            ],
            Phase::Six->value => [
                Type::Getter->value,
                Type::Full->value,
            ],
        ];
    }
}
