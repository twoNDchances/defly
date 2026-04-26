<?php

namespace App\Policies;

use App\Models\Engine;
use App\Traits\Policies\Basic;
use App\Traits\Policies\Extra;

class EnginePolicy
{
    use Basic, Extra;

    public function getModel()
    {
        return Engine::class;
    }
}
