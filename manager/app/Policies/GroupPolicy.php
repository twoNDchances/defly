<?php

namespace App\Policies;

use App\Models\Group;
use App\Traits\Policies\Basic;

class GroupPolicy
{
    use Basic;

    public function getModel()
    {
        return Group::class;
    }
}
