<?php

namespace Tests\Feature\Models;

use ReflectionEnum;
use Tests\Support\ModelTestHelpers;
use Tests\TestCase;

class EnumsTest extends TestCase
{
    use ModelTestHelpers;

    public function test_all_backed_enums_have_unique_values(): void
    {
        foreach ($this->enumClasses() as $enumClass) {
            $values = array_map(fn ($case) => $case->getBackingValue(), (new ReflectionEnum($enumClass))->getCases());

            $this->assertNotEmpty($values, "{$enumClass} should define cases.");
            $this->assertSame($values, array_values(array_unique($values)), "{$enumClass} has duplicate values.");
        }
    }
}
