<?php

namespace Tests\Support;

use App\Models\Target;
use RuntimeException;

class ThrowingTargetForTrace extends Target
{
    public function getAttribute($key)
    {
        if ($key === 'datatype') {
            throw new RuntimeException('trace failed');
        }

        return parent::getAttribute($key);
    }
}
