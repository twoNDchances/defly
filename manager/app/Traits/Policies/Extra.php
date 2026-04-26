<?php

namespace App\Traits\Policies;

use App\Models\User;

trait Extra
{
    /**
     * Determine whether the user can clone the model.
     */
    public function clone(User $user, $model): bool
    {
        return $this->checkAccess($user, $model, 'clone');
    }
}
