<?php

namespace App\Policies;

use App\Models\Decision;
use App\Models\User;
use App\Services\Security;
use App\Traits\Policies\Basic;
use App\Traits\Policies\Extra;

class DecisionPolicy
{
    use Basic, Extra;

    public function getModel()
    {
        return Decision::class;
    }

    public function implement(User $user, Decision $decision): bool
    {
        return $this->checkAccess($user, $decision, 'implement');
    }

    public function implementAny(User $user): bool
    {
        return Security::can($this->getModel(), 'implementAny', $user);
    }

    public function suspend(User $user, Decision $decision): bool
    {
        return $this->checkAccess($user, $decision, 'suspend');
    }

    public function suspendAny(User $user): bool
    {
        return Security::can($this->getModel(), 'suspendAny', $user);
    }
}
