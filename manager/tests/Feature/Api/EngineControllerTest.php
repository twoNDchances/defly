<?php

namespace Tests\Feature\Api;

use App\Enums\Datatype;
use App\Enums\Engine\Hash;
use App\Enums\Engine\Type as EngineType;
use App\Models\Engine;
use Illuminate\Support\Str;

class EngineControllerTest extends ApiTestCase
{
    public function test_engines_payload_endpoint_is_accessible(): void
    {
        $this->apiJson('GET', $this->apiRoute('engines', 'payload'))->assertOk();
    }

    public function test_engines_store_supports_all_engine_types(): void
    {
        foreach ($this->enginePayloads() as $payload) {
            $payload['name'] = $payload['name'].'-'.Str::lower(Str::random(6));

            $response = $this->apiJson('POST', $this->apiRoute('engines', 'store'), [], $payload)
                ->assertCreated();

            $engine = Engine::query()->findOrFail((string) $response->json('id'));
            $this->assertSame($payload['type'], $engine->type);
        }
    }

    public function test_engines_reject_invalid_type_for_input_datatype(): void
    {
        $this->apiJson('POST', $this->apiRoute('engines', 'store'), [], [
            'name' => 'invalid-engine',
            'input_datatype' => Datatype::String->value,
            'type' => EngineType::Addition->value,
            'output_datatype' => Datatype::Number->value,
        ])->assertUnprocessable()->assertJsonValidationErrors(['type']);
    }

    public function test_engines_api_crud_validation_and_put_patch_behavior(): void
    {
        $this->apiJson('GET', $this->apiRoute('engines', 'index'))->assertOk();
        $this->apiJson('POST', $this->apiRoute('engines', 'store'), [], [])->assertUnprocessable();

        $storePayload = [
            'name' => 'number-addition-engine',
            'input_datatype' => Datatype::Number->value,
            'type' => EngineType::Addition->value,
            'digit' => 10,
            'output_datatype' => Datatype::Number->value,
            'description' => 'Addition engine.',
        ];

        $storeResponse = $this->apiJson('POST', $this->apiRoute('engines', 'store'), [], $storePayload)
            ->assertCreated();

        $engineId = (string) $storeResponse->json('id');
        $engine = Engine::query()->findOrFail($engineId);
        $this->assertSame(10.0, (float) data_get($engine->configurations, 'digit'));

        $this->apiJson('GET', $this->apiRoute('engines', 'show'), ['engine' => $engineId])
            ->assertOk()
            ->assertJsonPath('id', $engineId);

        $this->apiJson('PATCH', $this->apiRoute('engines', 'update'), ['engine' => $engineId], [
            'description' => 'Patched engine.',
        ])->assertOk()->assertJsonPath('description', 'Patched engine.');

        $this->apiJson('PUT', $this->apiRoute('engines', 'update'), ['engine' => $engineId], [
            'description' => 'Only description',
        ])->assertUnprocessable();

        $putPayload = [
            'name' => 'string-hash-engine',
            'input_datatype' => Datatype::String->value,
            'type' => EngineType::Hash->value,
            'hash_method' => Hash::Sha256->value,
            'output_datatype' => Datatype::String->value,
            'description' => 'Hash engine.',
        ];

        $this->apiJson('PUT', $this->apiRoute('engines', 'update'), ['engine' => $engineId], $putPayload)
            ->assertOk()
            ->assertJsonPath('name', 'string-hash-engine');

        $this->apiJson('DELETE', $this->apiRoute('engines', 'destroy'), ['engine' => $engineId])->assertNoContent();
        $this->assertDatabaseMissing('engines', ['id' => $engineId]);
    }

    private function enginePayloads(): array
    {
        return [
            ['name' => 'array-index-of', 'input_datatype' => Datatype::Array->value, 'type' => EngineType::IndexOf->value, 'position' => 0, 'output_datatype' => Datatype::String->value],
            ['name' => 'array-merge', 'input_datatype' => Datatype::Array->value, 'type' => EngineType::Merge->value, 'separator' => ',', 'output_datatype' => Datatype::String->value],
            ['name' => 'number-addition', 'input_datatype' => Datatype::Number->value, 'type' => EngineType::Addition->value, 'digit' => 10, 'output_datatype' => Datatype::Number->value],
            ['name' => 'number-subtraction', 'input_datatype' => Datatype::Number->value, 'type' => EngineType::Subtraction->value, 'digit' => 5, 'output_datatype' => Datatype::Number->value],
            ['name' => 'number-multiplication', 'input_datatype' => Datatype::Number->value, 'type' => EngineType::Multiplication->value, 'digit' => 2, 'output_datatype' => Datatype::Number->value],
            ['name' => 'number-division', 'input_datatype' => Datatype::Number->value, 'type' => EngineType::Division->value, 'digit' => 2, 'output_datatype' => Datatype::Number->value],
            ['name' => 'number-power-of', 'input_datatype' => Datatype::Number->value, 'type' => EngineType::PowerOf->value, 'digit' => 2, 'output_datatype' => Datatype::Number->value],
            ['name' => 'number-remainder', 'input_datatype' => Datatype::Number->value, 'type' => EngineType::Remainder->value, 'digit' => 2, 'output_datatype' => Datatype::Number->value],
            ['name' => 'number-to-string', 'input_datatype' => Datatype::Number->value, 'type' => EngineType::ToString->value, 'output_datatype' => Datatype::String->value],
            ['name' => 'string-lower', 'input_datatype' => Datatype::String->value, 'type' => EngineType::Lower->value, 'output_datatype' => Datatype::String->value],
            ['name' => 'string-upper', 'input_datatype' => Datatype::String->value, 'type' => EngineType::Upper->value, 'output_datatype' => Datatype::String->value],
            ['name' => 'string-capitalize', 'input_datatype' => Datatype::String->value, 'type' => EngineType::Capitalize->value, 'output_datatype' => Datatype::String->value],
            ['name' => 'string-trim', 'input_datatype' => Datatype::String->value, 'type' => EngineType::Trim->value, 'output_datatype' => Datatype::String->value],
            ['name' => 'string-trim-left', 'input_datatype' => Datatype::String->value, 'type' => EngineType::TrimLeft->value, 'output_datatype' => Datatype::String->value],
            ['name' => 'string-trim-right', 'input_datatype' => Datatype::String->value, 'type' => EngineType::TrimRight->value, 'output_datatype' => Datatype::String->value],
            ['name' => 'string-remove-whitespace', 'input_datatype' => Datatype::String->value, 'type' => EngineType::RemoveWhitespace->value, 'output_datatype' => Datatype::String->value],
            ['name' => 'string-length', 'input_datatype' => Datatype::String->value, 'type' => EngineType::Length->value, 'output_datatype' => Datatype::Number->value],
            ['name' => 'string-hash', 'input_datatype' => Datatype::String->value, 'type' => EngineType::Hash->value, 'hash_method' => Hash::Sha256->value, 'output_datatype' => Datatype::String->value],
            ['name' => 'string-split', 'input_datatype' => Datatype::String->value, 'type' => EngineType::Split->value, 'separator' => ',', 'output_datatype' => Datatype::Array->value],
        ];
    }
}
