<?php

namespace Tests\Feature\Validators;

use Illuminate\Support\Str;
use Tests\Support\PatternValidatorHarness;
use Tests\TestCase;

class PatternValidatorTest extends TestCase
{
    public function test_pattern_validator_rules_are_resolvable(): void
    {
        $this->assertArrayHasKey('targets.*', PatternValidatorHarness::pattern());
        $this->assertNotEmpty(PatternValidatorHarness::nameRule((string) Str::uuid()));
        $this->assertNotEmpty(PatternValidatorHarness::targetItemRule());
    }
}
