<?php

namespace App\Policies;

use App\Models\Target;
use App\Traits\Policies\Basic;

class TargetPolicy
{
    use Basic;

    public function getModel()
    {
        return Target::class;
    }
}
