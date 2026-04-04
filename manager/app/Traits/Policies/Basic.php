<?php

namespace App\Traits\Policies;

use App\Models\User;
use App\Services\Security;

trait Basic
{
    use Access;

    /**
     * Determine whether the user can use any models.
     */
    public function all(User $user): bool
    {
        return Security::can($this->getModel(), 'all', $user);
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return Security::can($this->getModel(), 'viewAny', $user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, $model): bool
    {
        return $this->checkAccess($user, $model, 'view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return Security::can($this->getModel(), 'create', $user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, $model): bool
    {
        return $this->checkAccess($user, $model, 'update');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return Security::can($this->getModel(), 'deleteAny', $user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, $model): bool
    {
        return $this->checkAccess($user, $model, 'delete');
    }
}
