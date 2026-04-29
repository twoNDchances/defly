<?php

namespace App\Policies;

use App\Enums\Principle\ValidationStatus;
use App\Models\Principle;
use App\Models\User;
use App\Services\Security;
use App\Traits\Policies\Basic;
use App\Traits\Policies\Extra;

class PrinciplePolicy
{
    use Basic, Extra;

    public function getModel()
    {
        return Principle::class;
    }

    private const PROTECTED_STATUSES = [
        ValidationStatus::Pending,
        ValidationStatus::Validating,
    ];

    protected function isProtectedStatus(Principle $principle): bool
    {
        return in_array($principle->validation_status, self::PROTECTED_STATUSES, true);
    }

    public function update(User $user, Principle $principle): bool
    {
        if ($this->isProtectedStatus($principle)) {
            return false;
        }

        return $this->checkAccess($user, $principle, 'update');
    }

    public function delete(User $user, Principle $principle): bool
    {
        if ($this->isProtectedStatus($principle)) {
            return false;
        }

        return $this->checkAccess($user, $principle, 'delete');
    }

    public function validate(User $user, Principle $principle): bool
    {
        if ($this->isProtectedStatus($principle)) {
            return false;
        }

        return $this->checkAccess($user, $principle, 'validate');
    }

    public function validateAny(User $user): bool
    {
        return Security::can($this->getModel(), 'validateAny', $user);
    }
}
