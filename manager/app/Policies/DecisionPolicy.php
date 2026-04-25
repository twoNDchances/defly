<?php

namespace App\Policies;

use App\Models\Decision;
use App\Traits\Policies\Basic;

class DecisionPolicy
{
    use Basic;

    public function getModel()
    {
        return Decision::class;
    }
}
