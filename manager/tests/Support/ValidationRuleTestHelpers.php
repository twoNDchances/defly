<?php

namespace Tests\Support;

use App\Enums\Phase;
use App\Enums\Type as TargetType;
use App\Models\Target;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

trait ValidationRuleTestHelpers
{
    protected function assertValidatorPasses(array $data, array $rules): void
    {
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes(), (string) $validator->errors());
    }

    protected function assertValidatorFails(array $data, array $rules, string $field): void
    {
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey($field, $validator->errors()->toArray());
    }

    protected function validationTarget(int $phase, string $datatype): Target
    {
        return Target::query()->create([
            'name' => 'target-'.Str::lower(Str::random(6)),
            'phase' => $phase,
            'type' => TargetType::Getter->value,
            'datatype' => $datatype,
        ]);
    }
}
