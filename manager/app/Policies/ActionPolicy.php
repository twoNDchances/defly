<?php

namespace App\Policies;

use App\Models\Action;
use App\Traits\Policies\Basic;

class ActionPolicy
{
    use Basic;

    public function getModel()
    {
        return Action::class;
    }
}
