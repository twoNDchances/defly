<?php

namespace Tests\Feature\Rules;

use App\Enums\Phase;
use App\Enums\Type as TargetType;
use App\Rules\Target\TypeField as TargetTypeField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ValidationRuleTestHelpers;
use Tests\TestCase;

class TargetTypeFieldTest extends TestCase
{
    use RefreshDatabase;
    use ValidationRuleTestHelpers;

    public function test_target_type_field_limits_types_by_phase(): void
    {
        $this->assertValidatorPasses([
            'phase' => Phase::Two->value,
            'type' => TargetType::Header->value,
        ], ['type' => [new TargetTypeField()]]);

        $this->assertValidatorFails([
            'phase' => Phase::One->value,
            'type' => TargetType::Header->value,
        ], ['type' => [new TargetTypeField()]], 'type');
    }

    public function test_target_type_field_ignores_incomplete_or_unknown_context(): void
    {
        $this->assertValidatorPasses(['phase' => null, 'type' => TargetType::Getter->value], ['type' => [new TargetTypeField()]]);
        $this->assertValidatorPasses(['phase' => 99, 'type' => TargetType::Getter->value], ['type' => [new TargetTypeField()]]);
    }
}
