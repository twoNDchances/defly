<?php

namespace App\Policies;

use App\Models\Timeline;
use App\Models\User;
use App\Traits\Policies\Basic;

class TimelinePolicy
{
    use Basic;

    public function getModel()
    {
        return Timeline::class;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, $model): bool
    {
        return false;
    }
}
