<?php

namespace App\Policies;

use App\Models\Target;
use App\Traits\Policies\Basic;
use App\Traits\Policies\Extra;

class TargetPolicy
{
    use Basic, Extra;

    public function getModel()
    {
        return Target::class;
    }
}
