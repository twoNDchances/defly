<?php

namespace App\Policies;

use App\Models\Policy;
use App\Traits\Policies\Basic;
use App\Traits\Policies\Relationship;

class PolicyPolicy
{
    use Basic, Relationship;

    public function getModel()
    {
        return Policy::class;
    }
}
