<?php

namespace App\Policies;

use App\Models\Key;
use App\Traits\Policies\Basic;

class KeyPolicy
{
    use Basic;

    public function getModel()
    {
        return Key::class;
    }
}
