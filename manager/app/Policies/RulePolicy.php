<?php

namespace App\Policies;

use App\Models\Rule;
use App\Traits\Policies\Basic;

class RulePolicy
{
    use Basic;

    public function getModel()
    {
        return Rule::class;
    }
}
