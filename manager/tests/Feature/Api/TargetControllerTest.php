<?php

namespace Tests\Feature\Api;

use App\Enums\Datatype;
use App\Enums\Phase;
use App\Enums\Type as TargetType;
use App\Models\Pattern;
use App\Models\Target;
use App\Models\Wordlist;
use Illuminate\Support\Str;

class TargetControllerTest extends ApiTestCase
{
    public function test_targets_payload_endpoint_is_accessible(): void
    {
        $this->apiJson('GET', $this->apiRoute('targets', 'payload'))->assertOk();
    }

    public function test_targets_validate_phase_type_and_conditional_fields(): void
    {
        $this->apiJson('POST', $this->apiRoute('targets', 'store'), [], [
            'phase' => Phase::One->value,
            'type' => TargetType::Header->value,
            'name' => 'invalid-target',
            'datatype' => Datatype::String->value,
        ])->assertUnprocessable()->assertJsonValidationErrors(['type']);

        $this->apiJson('POST', $this->apiRoute('targets', 'store'), [], [
            'phase' => Phase::One->value,
            'type' => TargetType::Full->value,
            'name' => 'missing-pattern',
            'datatype' => Datatype::String->value,
        ])->assertUnprocessable()->assertJsonValidationErrors(['pattern_id']);

        $arrayStore = $this->apiJson('POST', $this->apiRoute('targets', 'store'), [], [
            'phase' => Phase::One->value,
            'type' => TargetType::Getter->value,
            'name' => 'array-without-wordlist',
            'datatype' => Datatype::Array->value,
        ])->assertCreated();

        $arrayTarget = Target::query()->findOrFail((string) $arrayStore->json('id'));
        $this->assertSame(TargetType::Getter->value, $arrayTarget->type->value);
        $this->assertSame(Datatype::Array->value, $arrayTarget->datatype->value);
    }

    public function test_targets_api_crud_validation_and_put_patch_behavior(): void
    {
        $this->apiJson('GET', $this->apiRoute('targets', 'index'))->assertOk();
        $this->apiJson('POST', $this->apiRoute('targets', 'store'), [], [])->assertUnprocessable();

        $pattern = Pattern::query()->create([
            'name' => 'target-pattern-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'type' => TargetType::Full->value,
            'datatype' => Datatype::String->value,
            'description' => 'Pattern for target test.',
        ]);

        $storePayload = [
            'phase' => Phase::One->value,
            'type' => TargetType::Full->value,
            'pattern_id' => $pattern->id,
            'name' => 'request-full-target',
            'datatype' => Datatype::String->value,
            'description' => 'Target record.',
        ];

        $targetStore = $this->apiJson('POST', $this->apiRoute('targets', 'store'), [], $storePayload)
            ->assertCreated();
        $targetId = (string) $targetStore->json('id');

        $this->apiJson('GET', $this->apiRoute('targets', 'show'), ['target' => $targetId])
            ->assertOk()
            ->assertJsonPath('id', $targetId);

        $this->apiJson('PATCH', $this->apiRoute('targets', 'update'), ['target' => $targetId], [
            'description' => 'Patched target',
        ])->assertOk()->assertJsonPath('description', 'Patched target');

        $this->apiJson('PUT', $this->apiRoute('targets', 'update'), ['target' => $targetId], [
            'description' => 'Only description',
        ])->assertUnprocessable();

        $this->apiJson('PUT', $this->apiRoute('targets', 'update'), ['target' => $targetId], [
            'phase' => Phase::One->value,
            'type' => TargetType::Full->value,
            'pattern_id' => $pattern->id,
            'name' => 'request-full-target-replaced',
            'datatype' => Datatype::String->value,
            'description' => 'Replaced target',
        ])->assertOk()->assertJsonPath('name', 'request-full-target-replaced');

        $this->apiJson('DELETE', $this->apiRoute('targets', 'destroy'), ['target' => $targetId])->assertNoContent();
        $this->assertDatabaseMissing('targets', ['id' => $targetId]);
    }

    public function test_targets_can_store_array_getter_with_wordlist(): void
    {
        $wordlist = Wordlist::query()->create([
            'name' => 'target-wordlist-'.Str::lower(Str::random(6)),
            'type' => 'json',
            'word_json' => [['word' => 'admin']],
            'description' => 'Wordlist for target.',
        ]);

        $response = $this->apiJson('POST', $this->apiRoute('targets', 'store'), [], [
            'phase' => Phase::One->value,
            'type' => TargetType::Getter->value,
            'name' => 'request-tags',
            'datatype' => Datatype::Array->value,
            'wordlist_id' => $wordlist->id,
        ])->assertCreated();

        $target = Target::query()->findOrFail((string) $response->json('id'));
        $this->assertSame(TargetType::Getter->value, $target->type->value);
        $this->assertSame(Datatype::Array->value, $target->datatype->value);
    }
}
