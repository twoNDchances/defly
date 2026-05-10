<?php

namespace Tests\Feature\Api;

use App\Enums\Datatype;
use App\Enums\Phase;
use App\Enums\Rule\Comparator;
use App\Enums\Type as TargetType;
use App\Models\Rule;
use App\Models\Target;
use App\Models\Wordlist;
use Illuminate\Support\Str;

class RuleControllerTest extends ApiTestCase
{
    public function test_rules_payload_endpoint_is_accessible(): void
    {
        $this->apiJson('GET', $this->apiRoute('rules', 'payload'))->assertOk();
    }

    public function test_rules_store_supports_all_comparators(): void
    {
        $stringTarget = $this->createTargetForRule('rule-target-string', Datatype::String->value);
        $numberTarget = $this->createTargetForRule('rule-target-number', Datatype::Number->value);
        $arrayTarget = $this->createTargetForRule('rule-target-array', Datatype::Array->value);
        $wordlist = $this->createWordlist('rule-wordlist');

        foreach ($this->rulePayloads($stringTarget, $numberTarget, $arrayTarget, $wordlist) as $payload) {
            $payload['name'] = $payload['name'].'-'.Str::lower(Str::random(6));
            $this->apiJson('POST', $this->apiRoute('rules', 'store'), [], $payload)->assertCreated();
        }
    }

    public function test_rules_reject_invalid_comparator_for_target_datatype(): void
    {
        $numberTarget = $this->createTargetForRule('invalid-comparator-target', Datatype::Number->value);

        $this->apiJson('POST', $this->apiRoute('rules', 'store'), [], [
            'name' => 'invalid-comparator',
            'phase' => Phase::One->value,
            'target_id' => $numberTarget->id,
            'comparator' => Comparator::Mirror->value,
            'is_inversed' => false,
            'string_value' => 'needle',
        ])->assertUnprocessable()->assertJsonValidationErrors(['comparator']);
    }

    public function test_rules_api_crud_validation_and_put_patch_behavior(): void
    {
        $this->apiJson('GET', $this->apiRoute('rules', 'index'))->assertOk();
        $this->apiJson('POST', $this->apiRoute('rules', 'store'), [], [])->assertUnprocessable();

        $target = $this->createTargetForRule('mirror-rule-target', Datatype::String->value);

        $ruleStorePayload = [
            'name' => 'mirror-rule',
            'phase' => Phase::One->value,
            'target_id' => $target->id,
            'comparator' => Comparator::Mirror->value,
            'is_inversed' => false,
            'string_value' => 'needle',
            'description' => 'Rule record.',
        ];

        $ruleStore = $this->apiJson('POST', $this->apiRoute('rules', 'store'), [], $ruleStorePayload)
            ->assertCreated();
        $ruleId = (string) $ruleStore->json('id');

        $rule = Rule::query()->findOrFail($ruleId);
        $this->assertSame('needle', data_get($rule->configurations, 'string'));

        $this->apiJson('GET', $this->apiRoute('rules', 'show'), ['rule' => $ruleId])
            ->assertOk()
            ->assertJsonPath('id', $ruleId);

        $this->apiJson('PATCH', $this->apiRoute('rules', 'update'), ['rule' => $ruleId], [
            'description' => 'Patched rule',
        ])->assertOk()->assertJsonPath('description', 'Patched rule');

        $this->apiJson('PUT', $this->apiRoute('rules', 'update'), ['rule' => $ruleId], [
            'description' => 'Only description',
        ])->assertUnprocessable();

        $this->apiJson('PUT', $this->apiRoute('rules', 'update'), ['rule' => $ruleId], [
            'name' => 'mirror-rule-replaced',
            'phase' => Phase::One->value,
            'target_id' => $target->id,
            'comparator' => Comparator::Mirror->value,
            'is_inversed' => true,
            'string_value' => 'replacement',
            'description' => 'Replaced rule',
        ])->assertOk()->assertJsonPath('name', 'mirror-rule-replaced');

        $this->apiJson('DELETE', $this->apiRoute('rules', 'destroy'), ['rule' => $ruleId])->assertNoContent();
        $this->assertDatabaseMissing('rules', ['id' => $ruleId]);
    }

    private function rulePayloads(Target $stringTarget, Target $numberTarget, Target $arrayTarget, Wordlist $wordlist): array
    {
        return [
            ['name' => 'array-similar', 'phase' => Phase::One->value, 'target_id' => $arrayTarget->id, 'comparator' => Comparator::Similar->value, 'is_inversed' => false, 'wordlist_id' => $wordlist->id],
            ['name' => 'array-contains', 'phase' => Phase::One->value, 'target_id' => $arrayTarget->id, 'comparator' => Comparator::Contains->value, 'is_inversed' => false, 'string_value' => 'blocked-token'],
            ['name' => 'array-match', 'phase' => Phase::One->value, 'target_id' => $arrayTarget->id, 'comparator' => Comparator::Match->value, 'is_inversed' => false, 'string_value' => 'exact-value'],
            ['name' => 'array-search', 'phase' => Phase::One->value, 'target_id' => $arrayTarget->id, 'comparator' => Comparator::Search->value, 'is_inversed' => false, 'wordlist_id' => $wordlist->id],
            ['name' => 'number-equal', 'phase' => Phase::One->value, 'target_id' => $numberTarget->id, 'comparator' => Comparator::Equal->value, 'is_inversed' => false, 'number_value' => 10],
            ['name' => 'number-greater-than', 'phase' => Phase::One->value, 'target_id' => $numberTarget->id, 'comparator' => Comparator::GreaterThan->value, 'is_inversed' => false, 'number_value' => 10],
            ['name' => 'number-less-than', 'phase' => Phase::One->value, 'target_id' => $numberTarget->id, 'comparator' => Comparator::LessThan->value, 'is_inversed' => false, 'number_value' => 10],
            ['name' => 'number-greater-than-or-equal', 'phase' => Phase::One->value, 'target_id' => $numberTarget->id, 'comparator' => Comparator::GreaterThanOrEqual->value, 'is_inversed' => false, 'number_value' => 10],
            ['name' => 'number-less-than-or-equal', 'phase' => Phase::One->value, 'target_id' => $numberTarget->id, 'comparator' => Comparator::LessThanOrEqual->value, 'is_inversed' => false, 'number_value' => 10],
            ['name' => 'number-in-range', 'phase' => Phase::One->value, 'target_id' => $numberTarget->id, 'comparator' => Comparator::InRange->value, 'is_inversed' => false, 'number_from_value' => 1, 'number_to_value' => 10],
            ['name' => 'string-mirror', 'phase' => Phase::One->value, 'target_id' => $stringTarget->id, 'comparator' => Comparator::Mirror->value, 'is_inversed' => false, 'string_value' => 'needle'],
            ['name' => 'string-starts-with', 'phase' => Phase::One->value, 'target_id' => $stringTarget->id, 'comparator' => Comparator::StartsWith->value, 'is_inversed' => false, 'string_value' => 'Bearer '],
            ['name' => 'string-ends-with', 'phase' => Phase::One->value, 'target_id' => $stringTarget->id, 'comparator' => Comparator::EndsWith->value, 'is_inversed' => false, 'string_value' => '.php'],
            ['name' => 'string-check', 'phase' => Phase::One->value, 'target_id' => $stringTarget->id, 'comparator' => Comparator::Check->value, 'is_inversed' => false, 'wordlist_id' => $wordlist->id],
            ['name' => 'string-reg-exp', 'phase' => Phase::One->value, 'target_id' => $stringTarget->id, 'comparator' => Comparator::RegExp->value, 'is_inversed' => false, 'string_value' => '/admin/'],
            ['name' => 'string-check-reg-exp', 'phase' => Phase::One->value, 'target_id' => $stringTarget->id, 'comparator' => Comparator::CheckRegExp->value, 'is_inversed' => false, 'wordlist_id' => $wordlist->id],
        ];
    }

    private function createTargetForRule(string $name, string $datatype): Target
    {
        $payload = [
            'phase' => Phase::One->value,
            'type' => TargetType::Getter->value,
            'name' => $name.'-'.Str::lower(Str::random(6)),
            'datatype' => $datatype,
            'description' => 'Target for rule testing',
        ];

        if ($datatype === Datatype::Array->value) {
            $payload['wordlist_id'] = $this->createWordlist('array-target-wordlist')->id;
        }

        $response = $this->apiJson('POST', $this->apiRoute('targets', 'store'), [], $payload)->assertCreated();

        return Target::query()->findOrFail((string) $response->json('id'));
    }

    private function createWordlist(string $name): Wordlist
    {
        $response = $this->apiJson('POST', $this->apiRoute('wordlists', 'store'), [], [
            'name' => $name.'-'.Str::lower(Str::random(6)),
            'type' => 'json',
            'word_json' => [
                ['word' => 'admin'],
                ['word' => 'debug'],
            ],
            'description' => 'Wordlist for rule testing',
        ])->assertCreated();

        return Wordlist::query()->findOrFail((string) $response->json('id'));
    }
}
