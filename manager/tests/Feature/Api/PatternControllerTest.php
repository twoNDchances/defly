<?php

namespace Tests\Feature\Api;

use App\Enums\Datatype;
use App\Enums\Phase;
use App\Enums\Type as TargetType;
use App\Models\Pattern;

class PatternControllerTest extends ApiTestCase
{
    public function test_patterns_api_list_and_get_and_restricted_methods(): void
    {
        $pattern = Pattern::query()->create([
            'name' => 'request-full-pattern',
            'phase' => Phase::One->value,
            'type' => TargetType::Full->value,
            'datatype' => Datatype::String->value,
            'description' => 'Pattern for testing.',
        ]);

        $this->apiJson('GET', $this->apiRoute('patterns', 'index'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $pattern->id);

        $this->apiJson('GET', $this->apiRoute('patterns', 'show'), ['pattern' => $pattern->id])
            ->assertOk()
            ->assertJsonPath('id', $pattern->id);

        $this->apiJsonToUrl('POST', route($this->apiRoute('patterns', 'index')), [
            'name' => 'forbidden-pattern',
        ])->assertMethodNotAllowed();

        $this->apiJsonToUrl('PATCH', route($this->apiRoute('patterns', 'show'), ['pattern' => $pattern->id]), [
            'name' => 'cannot-update',
        ])->assertMethodNotAllowed();
    }
}
