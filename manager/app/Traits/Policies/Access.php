<?php

namespace App\Traits\Policies;

use App\Models\User;
use App\Services\Identification;
use App\Services\Security;
use Nette\NotImplementedException;

trait Access
{
    public function getModel()
    {
        throw new NotImplementedException('You need to specific the model');
    }

    public function checkAccess(User $user, $model, $action): bool
    {
        if (in_array($action, ['update', 'delete'], true) && data_get($model, 'is_locked') === true) {
            return false;
        }

        if (Identification::isRoot()) {
            return true;
        }
        $permission = Security::can($this->getModel(), $action, $user);

        return $permission;
    }
}
