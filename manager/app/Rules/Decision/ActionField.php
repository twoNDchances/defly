<?php

namespace App\Rules\Decision;

use App\Enums\Decision\Action;
use App\Enums\Decision\Direction;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ActionField implements DataAwareRule, ValidationRule
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
        $direction = $this->data['direction'] ?? null;

        if (! is_string($direction) || ! is_string($value)) {
            return;
        }

        $allowed = self::actionsPerDirection()[$direction] ?? null;

        if ($allowed === null) {
            return;
        }

        if (! in_array($value, $allowed, true)) {
            $fail("The {$attribute} is invalid for the selected direction.");
        }
    }

    private static function actionsPerDirection(): array
    {
        return [
            Direction::Request->value => [
                Action::Allow->value,
                Action::Deny->value,
                Action::RewriteHeaders->value,
                Action::RewriteBody->value,
                Action::Redirect->value,
                Action::Cancel->value,
                Action::Rewrite->value,
                Action::Save->value,
            ],
            Direction::Response->value => [
                Action::Allow->value,
                Action::Deny->value,
                Action::RewriteHeaders->value,
                Action::RewriteBody->value,
                Action::EraseCookies->value,
                Action::ForceNoCache->value,
            ],
        ];
    }
}
