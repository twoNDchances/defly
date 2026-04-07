<?php

namespace App\Policies;

use App\Models\Engine;
use App\Traits\Policies\Basic;

class EnginePolicy
{
    use Basic;

    public function getModel()
    {
        return Engine::class;
    }
}
