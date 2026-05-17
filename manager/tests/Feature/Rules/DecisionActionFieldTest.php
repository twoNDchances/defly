<?php

namespace Tests\Feature\Rules;

use App\Enums\Decision\Action as DecisionAction;
use App\Enums\Decision\Direction;
use App\Rules\Decision\ActionField as DecisionActionField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ValidationRuleTestHelpers;
use Tests\TestCase;

class DecisionActionFieldTest extends TestCase
{
    use RefreshDatabase;
    use ValidationRuleTestHelpers;

    public function test_decision_action_field_limits_actions_by_direction(): void
    {
        $this->assertValidatorPasses([
            'direction' => Direction::Response->value,
            'action' => DecisionAction::ForceNoCache->value,
        ], ['action' => [new DecisionActionField()]]);

        $this->assertValidatorFails([
            'direction' => Direction::Response->value,
            'action' => DecisionAction::Redirect->value,
        ], ['action' => [new DecisionActionField()]], 'action');
    }

    public function test_decision_action_field_ignores_incomplete_or_unknown_context(): void
    {
        $this->assertValidatorPasses(['direction' => null, 'action' => DecisionAction::Allow->value], ['action' => [new DecisionActionField()]]);
        $this->assertValidatorPasses(['direction' => 'unknown', 'action' => DecisionAction::Allow->value], ['action' => [new DecisionActionField()]]);
    }
}
