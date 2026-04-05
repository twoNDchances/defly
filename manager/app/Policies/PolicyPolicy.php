<?php

namespace App\Policies;

use App\Models\Policy;
use App\Traits\Policies\Basic;

class PolicyPolicy
{
    use Basic;

    public function getModel()
    {
        return Policy::class;
    }
}
