<?php

namespace App\Policies;

use App\Models\Pattern;
use App\Models\User;
use App\Traits\Policies\Basic;

class PatternPolicy
{
    use Basic;

    public function getModel()
    {
        return Pattern::class;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, $model): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function delete(User $user, $model): bool
    {
        return false;
    }
}
