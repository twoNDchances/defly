<?php

namespace App\Policies;

use App\Models\Decision;
use App\Traits\Policies\Basic;
use App\Traits\Policies\Extra;

class DecisionPolicy
{
    use Basic, Extra;

    public function getModel()
    {
        return Decision::class;
    }
}
