<?php

namespace Tests\Feature\Rules;

use App\Rules\Permission\ActionField as PermissionActionField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ValidationRuleTestHelpers;
use Tests\TestCase;

class PermissionActionFieldTest extends TestCase
{
    use RefreshDatabase;
    use ValidationRuleTestHelpers;

    public function test_permission_action_field_limits_actions_by_model_policy(): void
    {
        $this->assertValidatorPasses([
            'applied_for' => 'Pattern',
            'action' => 'viewAny',
        ], ['action' => [new PermissionActionField()]]);

        $this->assertValidatorFails([
            'applied_for' => 'Pattern',
            'action' => 'create',
        ], ['action' => [new PermissionActionField()]], 'action');
    }
}
