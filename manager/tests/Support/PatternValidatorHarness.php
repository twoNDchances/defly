<?php

namespace Tests\Support;

use App\Traits\Validators\PatternValidator;

class PatternValidatorHarness
{
    use PatternValidator;

    public static function pattern(): array
    {
        return self::validatePattern();
    }

    public static function nameRule(?string $ignore = null): array
    {
        return self::validateName(ignore: $ignore);
    }

    public static function targetItemRule(): array
    {
        return self::validateTargetItem();
    }
}
