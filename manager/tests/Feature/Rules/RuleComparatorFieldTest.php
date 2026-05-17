<?php

namespace Tests\Feature\Rules;

use App\Enums\Datatype;
use App\Enums\Phase;
use App\Enums\Rule\Comparator;
use App\Rules\Rule\ComparatorField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\ValidationRuleTestHelpers;
use Tests\TestCase;

class RuleComparatorFieldTest extends TestCase
{
    use RefreshDatabase;
    use ValidationRuleTestHelpers;

    public function test_rule_comparator_field_limits_comparators_by_target_final_datatype(): void
    {
        $target = $this->validationTarget(Phase::One->value, Datatype::String->value);

        $this->assertValidatorPasses([
            'target_id' => $target->id,
            'comparator' => Comparator::Mirror->value,
        ], ['comparator' => [new ComparatorField()]]);

        $this->assertValidatorFails([
            'target_id' => $target->id,
            'comparator' => Comparator::GreaterThan->value,
        ], ['comparator' => [new ComparatorField()]], 'comparator');
    }

    public function test_rule_comparator_field_ignores_missing_and_unknown_targets(): void
    {
        $this->assertValidatorPasses([
            'target_id' => null,
            'comparator' => Comparator::Mirror->value,
        ], ['comparator' => [new ComparatorField()]]);

        $this->assertValidatorPasses([
            'target_id' => (string) Str::uuid(),
            'comparator' => Comparator::Mirror->value,
        ], ['comparator' => [new ComparatorField()]]);
    }
}
