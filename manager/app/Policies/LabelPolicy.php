<?php

namespace App\Policies;

use App\Models\Label;
use App\Traits\Policies\Basic;

class LabelPolicy
{
    use Basic;

    public function getModel()
    {
        return Label::class;
    }
}
