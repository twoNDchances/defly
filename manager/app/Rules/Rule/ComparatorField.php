<?php

namespace App\Rules\Rule;

use App\Enums\Datatype;
use App\Enums\Rule\Comparator;
use App\Models\Target;
use App\Services\Datatype as DatatypeService;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ComparatorField implements DataAwareRule, ValidationRule
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
        $targetId = $this->data['target_id'] ?? null;

        if (! is_string($targetId) || ! is_string($value)) {
            return;
        }

        $target = Target::query()->find($targetId);

        if (! $target) {
            return;
        }

        $datatype = DatatypeService::getFinal($target);
        $allowed = self::comparatorsPerDatatype()[$datatype] ?? null;

        if ($allowed === null) {
            return;
        }

        if (! in_array($value, $allowed, true)) {
            $fail("The {$attribute} is invalid for the selected target datatype.");
        }
    }

    private static function comparatorsPerDatatype(): array
    {
        return [
            Datatype::Array->value => [
                Comparator::Similar->value,
                Comparator::Contains->value,
                Comparator::Match->value,
                Comparator::Search->value,
            ],
            Datatype::Number->value => [
                Comparator::Equal->value,
                Comparator::GreaterThan->value,
                Comparator::LessThan->value,
                Comparator::GreaterThanOrEqual->value,
                Comparator::LessThanOrEqual->value,
                Comparator::InRange->value,
            ],
            Datatype::String->value => [
                Comparator::Mirror->value,
                Comparator::StartsWith->value,
                Comparator::EndsWith->value,
                Comparator::Check->value,
                Comparator::RegExp->value,
                Comparator::CheckRegExp->value,
            ],
        ];
    }
}
