<?php

namespace App\Policies;

use App\Models\Guard;
use App\Traits\Policies\Basic;

class GuardPolicy
{
    use Basic;

    public function getModel()
    {
        return Guard::class;
    }
}
