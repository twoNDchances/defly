<?php

namespace App\Traits\Policies;

use App\Models\User;
use App\Services\Security;

trait Relationship
{
    use Access;

    /**
     * Determine whether the user can attach the model.
     */
    public function attach(User $user, $model): bool
    {
        return $this->checkAccess($user, $model, 'attach');
    }

    /**
     * Determine whether the user can detach any model.
     */
    public function detachAny(User $user): bool
    {
        return Security::can($this->getModel(), 'detachAny', $user);
    }

    /**
     * Determine whether the user can detach the model.
     */
    public function detach(User $user, $model): bool
    {
        return $this->checkAccess($user, $model, 'detach');
    }
}
