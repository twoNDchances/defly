<?php

namespace Tests\Support;

use App\Models\Defender;

class RawDefenderForAuthorization extends Defender
{
    protected function casts()
    {
        return [];
    }
}
