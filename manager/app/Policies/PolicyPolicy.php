<?php

namespace App\Policies;

use App\Models\Policy;
use App\Traits\Policies\Basic;
use App\Traits\Policies\Relationship;

class PolicyPolicy
{
    use Basic;
    use Relationship;

    public function getModel()
    {
        return Policy::class;
    }
}
