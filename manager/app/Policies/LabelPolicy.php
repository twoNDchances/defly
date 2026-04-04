<?php

namespace App\Policies;

use App\Models\Label;
use App\Traits\Policies\Basic;
use App\Traits\Policies\Relationship;

class LabelPolicy
{
    use Basic, Relationship;

    public function getModel()
    {
        return Label::class;
    }
}
