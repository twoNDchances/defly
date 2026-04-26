<?php

namespace App\Policies;

use App\Enums\Policy\ValidationStatus;
use App\Models\Policy;
use App\Models\User;
use App\Services\Security;
use App\Traits\Policies\Basic;
use App\Traits\Policies\Extra;

class PolicyPolicy
{
    use Basic, Extra;

    public function getModel()
    {
        return Policy::class;
    }

    private const PROTECTED_STATUSES = [
        ValidationStatus::Pending,
        ValidationStatus::Validating,
    ];

    protected function isProtectedStatus(Policy $policy): bool
    {
        return in_array($policy->validation_status, self::PROTECTED_STATUSES, true);
    }

    public function update(User $user, Policy $policy): bool
    {
        if ($this->isProtectedStatus($policy)) {
            return false;
        }

        return $this->checkAccess($user, $policy, 'update');
    }

    public function delete(User $user, Policy $policy): bool
    {
        if ($this->isProtectedStatus($policy)) {
            return false;
        }

        return $this->checkAccess($user, $policy, 'delete');
    }

    public function validate(User $user, Policy $policy): bool
    {
        if ($this->isProtectedStatus($policy)) {
            return false;
        }

        return $this->checkAccess($user, $policy, 'validate');
    }

    public function validateAny(User $user): bool
    {
        return Security::can($this->getModel(), 'validateAny', $user);
    }
}
