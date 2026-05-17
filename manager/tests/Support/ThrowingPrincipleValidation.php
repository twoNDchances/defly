<?php

namespace Tests\Support;

use App\Jobs\PrincipleValidation;
use App\Models\Principle;
use RuntimeException;

class ThrowingPrincipleValidation extends PrincipleValidation
{
    protected function validatePrinciple(Principle $principle): array
    {
        throw new RuntimeException('validation exploded');
    }
}
