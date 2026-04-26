<?php

namespace App\Policies;

use App\Models\Rule;
use App\Traits\Policies\Basic;
use App\Traits\Policies\Extra;

class RulePolicy
{
    use Basic, Extra;

    public function getModel()
    {
        return Rule::class;
    }
}
