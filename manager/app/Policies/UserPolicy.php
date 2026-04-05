<?php

namespace App\Policies;

use App\Models\User;
use App\Traits\Policies\Basic;

class UserPolicy
{
    use Basic;

    public function getModel()
    {
        return User::class;
    }

    public function checkRoot(User $user, $model, $action): bool
    {
        if (! $user->is_root && $model->is_root) {
            return false;
        }
        if ($user->id == $model->id) {
            return false;
        }

        return $this->checkAccess($user, $model, $action);
    }

    public function view(User $user, $model): bool
    {
        return $this->checkRoot($user, $model, 'view');
    }

    public function update(User $user, $model): bool
    {
        return $this->checkRoot($user, $model, 'update');
    }

    public function delete(User $user, $model): bool
    {
        return $this->checkRoot($user, $model, 'delete');
    }

    public function attach(User $user, $model): bool
    {
        return $this->checkRoot($user, $model, 'attach');
    }

    public function detach(User $user, $model): bool
    {
        return $this->checkRoot($user, $model, 'detach');
    }
}
