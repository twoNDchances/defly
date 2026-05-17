<?php

namespace Tests\Support;

use App\Models\Principle;

class RawPrincipleForAuthorization extends Principle
{
    protected function casts()
    {
        return [];
    }
}
