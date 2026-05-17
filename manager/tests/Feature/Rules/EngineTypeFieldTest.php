<?php

namespace Tests\Feature\Rules;

use App\Enums\Datatype;
use App\Enums\Engine\Type as EngineType;
use App\Rules\Engine\TypeField as EngineTypeField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ValidationRuleTestHelpers;
use Tests\TestCase;

class EngineTypeFieldTest extends TestCase
{
    use RefreshDatabase;
    use ValidationRuleTestHelpers;

    public function test_engine_type_field_limits_types_by_input_datatype(): void
    {
        $this->assertValidatorPasses([
            'input_datatype' => Datatype::Number->value,
            'type' => EngineType::Addition->value,
        ], ['type' => [new EngineTypeField()]]);

        $this->assertValidatorFails([
            'input_datatype' => Datatype::Number->value,
            'type' => EngineType::Lower->value,
        ], ['type' => [new EngineTypeField()]], 'type');
    }

    public function test_engine_type_field_ignores_incomplete_or_unknown_context(): void
    {
        $this->assertValidatorPasses(['input_datatype' => null, 'type' => EngineType::Lower->value], ['type' => [new EngineTypeField()]]);
        $this->assertValidatorPasses(['input_datatype' => 'unknown', 'type' => EngineType::Lower->value], ['type' => [new EngineTypeField()]]);
    }
}
