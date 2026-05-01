<?php

namespace App\Rules\Permission;

use App\Services\Security;
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
        $appliedFor = $this->data['applied_for'] ?? null;

        if (! $appliedFor || ! array_key_exists($appliedFor, self::permissionList())) {
            return;
        }

        if (! array_key_exists((string) $value, self::permissionList()[$appliedFor])) {
            $fail("The {$attribute} is invalid for the selected applied for value.");
        }
    }

    private static function permissionList(): array
    {
        return Security::generatePermissionList(true);
    }
}
