<?php

namespace App\Policies;

use App\Models\Conservation;
use App\Models\User;
use App\Traits\Policies\Basic;

class ConservationPolicy
{
    use Basic;

    public function getModel()
    {
        return Conservation::class;
    }

    public function chat(User $user, ?Conservation $conservation = null): bool
    {
        return $this->checkAccess($user, $conservation, 'chat');
    }

    public function pin(User $user, Conservation $conservation): bool
    {
        return $this->checkAccess($user, $conservation, 'pin');
    }
}
