<?php

namespace Tests\Feature\Jobs;

use App\Enums\Action\Type as ActionType;
use App\Enums\Datatype;
use App\Enums\Engine\Type as EngineType;
use App\Enums\Phase;
use App\Enums\Principle\ValidationStatus;
use App\Enums\Rule\Comparator;
use App\Enums\Type as TargetType;
use App\Enums\Wordlist\Type as WordlistType;
use App\Jobs\PrincipleValidation;
use App\Models\Action;
use App\Models\Pattern;
use App\Models\Rule;
use App\Models\Target;
use App\Models\Wordlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery\VerificationDirector;
use Tests\Support\DomainTestHelpers;
use Tests\Support\ThrowingPrincipleValidation;
use Tests\Support\ThrowingTargetForTrace;
use Tests\Support\ThrowingWordlist;
use Tests\TestCase;

class PrincipleValidationJobTest extends TestCase
{
    use DomainTestHelpers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_validation_job_marks_valid_and_invalid_principles(): void
    {
        $validPrinciple = $this->principle();
        $target = $this->target(Datatype::String->value);
        $rule = $this->rule($target);
        $action = $this->action(ActionType::Allow->value);
        $rule->actions()->attach($action->id, ['order' => 1]);
        $validPrinciple->rules()->attach($rule->id, ['order' => 1]);

        (new PrincipleValidation($validPrinciple->id))->handle();
        $this->assertSame(ValidationStatus::Passed, $validPrinciple->refresh()->validation_status);
        $this->assertSame(0, $validPrinciple->refresh()->validation_details['summary']['errors_total']);

        $invalidPrinciple = $this->principle();
        (new PrincipleValidation($invalidPrinciple->id))->handle();
        $this->assertSame(ValidationStatus::Failed, $invalidPrinciple->refresh()->validation_status);
        $this->assertSame('principle.rules.empty', $invalidPrinciple->refresh()->validation_details['errors'][0]['code']);
    }

    public function test_validation_job_reports_invalid_dependency_shapes(): void
    {
        (new PrincipleValidation((string) Str::uuid(), 'nobody@example.com'))->handle();

        $invalidPhasePrinciple = $this->principle();
        DB::table('principles')->where('id', $invalidPhasePrinciple->id)->update(['phase' => 999]);

        (new PrincipleValidation($invalidPhasePrinciple->id))->handle();
        $this->assertSame(ValidationStatus::Failed, $invalidPhasePrinciple->refresh()->validation_status);
        $this->assertSame('principle.phase.invalid', $invalidPhasePrinciple->refresh()->validation_details['errors'][0]['code']);

        $job = new PrincipleValidation($invalidPhasePrinciple->id);
        $jsonWordlist = Wordlist::withoutEvents(fn () => Wordlist::query()->create([
            'name' => 'json-bad-'.Str::lower(Str::random(6)),
            'type' => WordlistType::Json->value,
            'word_count' => 9,
            'word_json' => ['bad' => 'shape'],
        ]));
        $fileWordlist = Wordlist::withoutEvents(fn () => Wordlist::query()->create([
            'name' => 'file-bad-'.Str::lower(Str::random(6)),
            'type' => WordlistType::File->value,
            'word_count' => -1,
            'word_file' => 'wordlists/missing.txt',
        ]));
        Storage::put('wordlists/two-lines.txt', "one\ntwo\n");
        $mismatchedFileWordlist = Wordlist::withoutEvents(fn () => Wordlist::query()->create([
            'name' => 'file-mismatch-'.Str::lower(Str::random(6)),
            'type' => WordlistType::File->value,
            'word_count' => 1,
            'word_file' => 'wordlists/two-lines.txt',
        ]));

        $this->assertSame('failed', $this->invokeJob($job, 'validateWordlist', $jsonWordlist->refresh(), 'rule')['status']);
        $this->assertSame('failed', $this->invokeJob($job, 'validateWordlist', $fileWordlist->refresh(), 'target')['status']);
        $this->assertSame('failed', $this->invokeJob($job, 'validateWordlist', $mismatchedFileWordlist->refresh(), 'target')['status']);

        $pattern = Pattern::withoutEvents(fn () => Pattern::query()->create([
            'name' => 'mismatch-pattern-'.Str::lower(Str::random(6)),
            'phase' => Phase::Two->value,
            'type' => TargetType::Header->value,
            'datatype' => Datatype::Number->value,
        ]));
        $invalidTarget = Target::withoutEvents(fn () => Target::query()->create([
            'name' => 'invalid-target-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'type' => TargetType::Getter->value,
            'datatype' => Datatype::Array->value,
            'pattern_id' => $pattern->id,
            'wordlist_id' => $jsonWordlist->id,
        ]));
        $targetResult = $this->invokeJob($job, 'validateTarget', $invalidTarget->refresh()->load(['pattern', 'wordlist', 'engines']));
        $this->assertSame('failed', $targetResult['status']);

        $invalidAction = new Action;
        $invalidAction->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'invalid-action',
            'type' => 'unknown-action',
        ], true);
        $this->assertSame('failed', $this->invokeJob($job, 'validateAction', $invalidAction)['status']);
        $validAction = $this->action(ActionType::Allow->value);

        $missingTargetRule = Rule::withoutEvents(fn () => Rule::query()->create([
            'name' => 'missing-target-rule-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'comparator' => Comparator::Mirror->value,
            'is_inversed' => false,
        ]));
        DB::table('rules')->where('id', $missingTargetRule->id)->update([
            'phase' => 999,
            'comparator' => 'unknown-comparator',
        ]);
        $targetCache = [];
        $missingRuleResult = $this->invokeJobWithReferences($job, 'validateRule', [
            $invalidPhasePrinciple->refresh(),
            $missingTargetRule->refresh()->load(['target', 'wordlist', 'actions']),
            Phase::One->value,
            &$targetCache,
        ]);
        $this->assertSame('failed', $missingRuleResult['status']);

        $wordlistRule = Rule::withoutEvents(fn () => Rule::query()->create([
            'name' => 'wordlist-rule-'.Str::lower(Str::random(6)),
            'phase' => Phase::Two->value,
            'target_id' => $invalidTarget->id,
            'comparator' => Comparator::Search->value,
            'wordlist_id' => $jsonWordlist->id,
            'is_inversed' => false,
        ]));
        $wordlistRule->actions()->attach($validAction->id, ['order' => 1]);
        $targetCache = [];
        $wordlistRuleResult = $this->invokeJobWithReferences($job, 'validateRule', [
            $invalidPhasePrinciple->refresh(),
            $wordlistRule->refresh()->load(['target.pattern', 'target.wordlist', 'target.engines', 'wordlist', 'actions']),
            Phase::One->value,
            &$targetCache,
        ]);
        $this->assertSame('failed', $wordlistRuleResult['status']);

        $throwingPrinciple = $this->principle();
        $log = Log::spy();
        (new ThrowingPrincipleValidation($throwingPrinciple->id))->handle();
        $this->assertSame('principle.validation.exception', $throwingPrinciple->refresh()->validation_details['errors'][0]['code']);

        $rawWordlist = new Wordlist;
        $rawWordlist->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'raw-wordlist',
            'type' => 'unknown-type',
            'word_count' => null,
        ], true);
        $this->assertSame('failed', $this->invokeJob($job, 'validateWordlist', $rawWordlist, 'raw')['status']);

        $requiredJsonWordlist = new Wordlist;
        $requiredJsonWordlist->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'required-json-wordlist',
            'type' => WordlistType::Json->value,
            'word_count' => 0,
            'word_json' => null,
        ], true);
        $this->assertSame('failed', $this->invokeJob($job, 'validateWordlist', $requiredJsonWordlist, 'raw')['status']);

        $wordInvalidWordlist = new Wordlist;
        $wordInvalidWordlist->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'word-invalid-wordlist',
            'type' => WordlistType::Json->value,
            'word_count' => 1,
            'word_json' => json_encode([['missing' => 'word']]),
        ], true);
        $this->assertSame('failed', $this->invokeJob($job, 'validateWordlist', $wordInvalidWordlist, 'raw')['status']);

        $requiredFileWordlist = new Wordlist;
        $requiredFileWordlist->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'required-file-wordlist',
            'type' => WordlistType::File->value,
            'word_count' => 0,
            'word_file' => '',
        ], true);
        $this->assertSame('failed', $this->invokeJob($job, 'validateWordlist', $requiredFileWordlist, 'raw')['status']);

        $rawInvalidTarget = new Target;
        $rawInvalidTarget->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'raw-invalid-target',
            'phase' => 999,
            'type' => 'bad-type',
            'datatype' => 'bad-datatype',
            'pattern_id' => null,
            'wordlist_id' => null,
        ], true);
        $rawInvalidTarget->setRelation('pattern', null);
        $rawInvalidTarget->setRelation('wordlist', null);
        $rawInvalidTarget->setRelation('engines', collect());
        $this->assertSame('failed', $this->invokeJob($job, 'validateTarget', $rawInvalidTarget)['status']);

        $missingPatternTarget = new Target;
        $missingPatternTarget->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'missing-pattern-target',
            'phase' => Phase::One->value,
            'type' => TargetType::Full->value,
            'datatype' => Datatype::String->value,
            'pattern_id' => (string) Str::uuid(),
            'wordlist_id' => null,
        ], true);
        $missingPatternTarget->setRelation('pattern', null);
        $missingPatternTarget->setRelation('wordlist', null);
        $missingPatternTarget->setRelation('engines', collect());
        $this->assertSame('failed', $this->invokeJob($job, 'validateTarget', $missingPatternTarget)['status']);

        $arrayTarget = new Target;
        $arrayTarget->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'array-target',
            'phase' => Phase::One->value,
            'type' => TargetType::Getter->value,
            'datatype' => Datatype::Array->value,
            'pattern_id' => null,
            'wordlist_id' => null,
        ], true);
        $arrayTarget->setRelation('pattern', null);
        $arrayTarget->setRelation('wordlist', null);
        $arrayTarget->setRelation('engines', collect());
        $this->assertSame('failed', $this->invokeJob($job, 'validateTarget', $arrayTarget)['status']);

        $engineTarget = $this->target(Datatype::String->value);
        $engineTarget->engines()->attach($this->engine(Datatype::Number->value, EngineType::Addition->value, Datatype::Number->value)->id, ['order' => 1]);
        $this->assertSame('failed', $this->invokeJob($job, 'validateTarget', $engineTarget->refresh()->load(['pattern', 'wordlist', 'engines']))['status']);

        $rawRuleTarget = $this->target(Datatype::String->value);
        $rawRule = new Rule;
        $rawRule->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'raw-rule',
            'phase' => Phase::One->value,
            'target_id' => $rawRuleTarget->id,
            'comparator' => Comparator::GreaterThan->value,
            'is_inversed' => false,
            'wordlist_id' => (string) Str::uuid(),
        ], true);
        $rawInvalidAction = new Action;
        $rawInvalidAction->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'raw-invalid-action',
            'type' => 'bad-action',
        ], true);
        $rawRule->setRelation('target', $rawRuleTarget->refresh()->load(['pattern', 'wordlist', 'engines']));
        $rawRule->setRelation('wordlist', null);
        $rawRule->setRelation('actions', collect([$rawInvalidAction]));
        $targetCache = [];
        $rawRuleResult = $this->invokeJobWithReferences($job, 'validateRule', [
            $invalidPhasePrinciple->refresh(),
            $rawRule,
            Phase::One->value,
            &$targetCache,
        ]);
        $this->assertSame('failed', $rawRuleResult['status']);

        $nestedPrinciple = $this->principle();
        $failedWordlist = Wordlist::withoutEvents(fn () => Wordlist::query()->create([
            'name' => 'nested-wordlist-'.Str::lower(Str::random(6)),
            'type' => WordlistType::Json->value,
            'word_count' => 3,
            'word_json' => [['word' => 'one']],
        ]));
        $nestedTarget = Target::withoutEvents(fn () => Target::query()->create([
            'name' => 'nested-target-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'type' => TargetType::Full->value,
            'datatype' => Datatype::String->value,
            'wordlist_id' => $failedWordlist->id,
        ]));
        $nestedRule = Rule::withoutEvents(fn () => Rule::query()->create([
            'name' => 'nested-rule-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'target_id' => $nestedTarget->id,
            'comparator' => Comparator::Search->value,
            'wordlist_id' => $failedWordlist->id,
            'is_inversed' => false,
        ]));
        $nestedRule = $nestedRule->refresh()->load(['target.pattern', 'target.wordlist', 'target.engines', 'wordlist']);
        $nestedRule->setRelation('actions', collect([$rawInvalidAction]));
        $nestedPrinciple->setRelation('rules', collect([$nestedRule]));

        $nestedResult = $this->invokeJob($job, 'validatePrinciple', $nestedPrinciple);
        $this->assertFalse($nestedResult['passed']);
        $this->assertGreaterThan(0, $nestedResult['details']['summary']['rules_failed']);
        $this->assertGreaterThan(0, $nestedResult['details']['summary']['targets_failed']);
        $this->assertGreaterThan(0, $nestedResult['details']['summary']['actions_failed']);

        $missingReferencedTargetRule = new Rule;
        $missingReferencedTargetRule->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'missing-referenced-target-rule',
            'phase' => Phase::One->value,
            'target_id' => (string) Str::uuid(),
            'comparator' => Comparator::Mirror->value,
            'is_inversed' => false,
            'wordlist_id' => null,
        ], true);
        $missingReferencedTargetRule->setRelation('target', null);
        $missingReferencedTargetRule->setRelation('wordlist', null);
        $missingReferencedTargetRule->setRelation('actions', collect());
        $targetCache = [];
        $this->assertSame('failed', $this->invokeJobWithReferences($job, 'validateRule', [
            $invalidPhasePrinciple->refresh(),
            $missingReferencedTargetRule,
            Phase::One->value,
            &$targetCache,
        ])['status']);

        $requiredWordlistRule = new Rule;
        $requiredWordlistRule->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'required-wordlist-rule',
            'phase' => Phase::One->value,
            'target_id' => $rawRuleTarget->id,
            'comparator' => Comparator::Search->value,
            'is_inversed' => false,
            'wordlist_id' => null,
        ], true);
        $requiredWordlistRule->setRelation('target', $rawRuleTarget->refresh()->load(['pattern', 'wordlist', 'engines']));
        $requiredWordlistRule->setRelation('wordlist', null);
        $requiredWordlistRule->setRelation('actions', collect());
        $targetCache = [];
        $this->assertSame('failed', $this->invokeJobWithReferences($job, 'validateRule', [
            $invalidPhasePrinciple->refresh(),
            $requiredWordlistRule,
            Phase::One->value,
            &$targetCache,
        ])['status']);

        $requiredPatternTarget = new Target;
        $requiredPatternTarget->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'required-pattern-target',
            'phase' => Phase::One->value,
            'type' => TargetType::Full->value,
            'datatype' => Datatype::String->value,
            'pattern_id' => null,
            'wordlist_id' => null,
        ], true);
        $requiredPatternTarget->setRelation('pattern', null);
        $requiredPatternTarget->setRelation('wordlist', null);
        $requiredPatternTarget->setRelation('engines', collect());
        $this->assertSame('failed', $this->invokeJob($job, 'validateTarget', $requiredPatternTarget)['status']);

        $missingWordlistTarget = new Target;
        $missingWordlistTarget->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'missing-wordlist-target',
            'phase' => Phase::One->value,
            'type' => TargetType::Getter->value,
            'datatype' => Datatype::Array->value,
            'pattern_id' => null,
            'wordlist_id' => (string) Str::uuid(),
        ], true);
        $missingWordlistTarget->setRelation('pattern', null);
        $missingWordlistTarget->setRelation('wordlist', null);
        $missingWordlistTarget->setRelation('engines', collect());
        $this->assertSame('failed', $this->invokeJob($job, 'validateTarget', $missingWordlistTarget)['status']);

        $throwingTarget = new ThrowingTargetForTrace;
        $throwingTarget->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'throwing-target',
            'phase' => Phase::One->value,
            'type' => TargetType::Getter->value,
            'datatype' => Datatype::String->value,
        ], true);
        $throwingTarget->setRelation('pattern', null);
        $throwingTarget->setRelation('wordlist', null);
        $throwingTarget->setRelation('engines', collect([$this->rawEngine(Datatype::String->value, Datatype::String->value)]));
        $this->assertSame('failed', $this->invokeJob($job, 'validateTarget', $throwingTarget)['status']);

        $throwingWordlist = new ThrowingWordlist;
        $throwingWordlist->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'throwing-wordlist',
            'type' => WordlistType::Json->value,
            'word_count' => 0,
        ], true);
        $this->assertSame('failed', $this->invokeJob($job, 'validateWordlist', $throwingWordlist, 'throwing')['status']);

        $errorExpectation = $log->shouldHaveReceived('error');
        $this->assertInstanceOf(VerificationDirector::class, $errorExpectation);
        $errorExpectation->once();
    }
}
