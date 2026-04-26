<?php

namespace App\Policies;

use App\Models\Action;
use App\Traits\Policies\Basic;
use App\Traits\Policies\Extra;

class ActionPolicy
{
    use Basic, Extra;

    public function getModel()
    {
        return Action::class;
    }
}
