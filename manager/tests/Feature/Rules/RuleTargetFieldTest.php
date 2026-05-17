<?php

namespace Tests\Feature\Rules;

use App\Enums\Datatype;
use App\Enums\Phase;
use App\Rules\Rule\TargetField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ValidationRuleTestHelpers;
use Tests\TestCase;

class RuleTargetFieldTest extends TestCase
{
    use RefreshDatabase;
    use ValidationRuleTestHelpers;

    public function test_rule_target_field_requires_target_in_selected_phase(): void
    {
        $target = $this->validationTarget(Phase::One->value, Datatype::String->value);

        $this->assertValidatorPasses([
            'phase' => Phase::One->value,
            'target_id' => $target->id,
        ], ['target_id' => [new TargetField()]]);

        $this->assertValidatorFails([
            'phase' => Phase::Two->value,
            'target_id' => $target->id,
        ], ['target_id' => [new TargetField()]], 'target_id');
    }
}
