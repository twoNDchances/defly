<?php

namespace Tests\Feature\Services;

use App\Enums\Datatype;
use App\Enums\Engine\Type as EngineType;
use App\Models\Target;
use App\Services\Datatype as DatatypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\DomainTestHelpers;
use Tests\TestCase;

class DatatypeServiceTest extends TestCase
{
    use DomainTestHelpers;
    use RefreshDatabase;

    public function test_tracing_resolves_valid_and_invalid_engine_chains(): void
    {
        $target = $this->target(Datatype::String->value);
        $lower = $this->engine(Datatype::String->value, EngineType::Lower->value, Datatype::String->value);
        $length = $this->engine(Datatype::String->value, EngineType::Length->value, Datatype::Number->value);
        $target->engines()->attach($lower->id, ['order' => 1]);
        $target->engines()->attach($length->id, ['order' => 2]);

        $this->assertSame(Datatype::Number->value, DatatypeService::getFinal($target->refresh()));
        $validTrace = DatatypeService::traceBack(Target::query()->whereKey($target->id)->get());
        $this->assertTrue($validTrace['status']);
        $this->assertCount(2, $validTrace['details'][$target->id]['engines']['valid']);

        $brokenTarget = $this->target(Datatype::String->value);
        $addition = $this->engine(Datatype::Number->value, EngineType::Addition->value, Datatype::Number->value);
        $brokenTarget->engines()->attach($addition->id, ['order' => 1]);

        $invalidTrace = DatatypeService::traceBack([$brokenTarget->refresh()]);
        $this->assertFalse($invalidTrace['status']);
        $this->assertSame('input_mismatch', $invalidTrace['details'][$brokenTarget->id]['engines']['invalid'][$addition->id]['reason']);
    }

    public function test_tracing_reports_missing_datatypes_previous_mismatches_and_unsorted_pivots(): void
    {
        $invalidTarget = new Target;
        $invalidTarget->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'invalid-datatype-target',
            'datatype' => null,
        ], true);
        $invalidTarget->setRelation('engines', collect());
        $this->assertNull(DatatypeService::getFinal($invalidTarget));

        $brokenTarget = new Target;
        $brokenTarget->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'broken-engine-target',
            'datatype' => Datatype::String->value,
        ], true);
        $missingDatatypeEngine = $this->rawEngine(null, Datatype::Number->value);
        $afterBrokenEngine = $this->rawEngine(Datatype::String->value, Datatype::Number->value);
        $brokenTarget->setRelation('engines', collect([$missingDatatypeEngine, $afterBrokenEngine]));

        $brokenTrace = DatatypeService::traceBack([$brokenTarget, 'not-a-target']);
        $this->assertFalse($brokenTrace['status']);
        $this->assertSame('missing_datatype', $brokenTrace['details'][$brokenTarget->id]['engines']['invalid'][$missingDatatypeEngine->id]['reason']);
        $this->assertSame('previous_mismatch', $brokenTrace['details'][$brokenTarget->id]['engines']['invalid'][$afterBrokenEngine->id]['reason']);

        $sortedTarget = new Target;
        $sortedTarget->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'sorted-engine-target',
            'datatype' => Datatype::String->value,
        ], true);
        $sortedTarget->setRelation('engines', collect([
            $this->rawEngine(Datatype::String->value, Datatype::String->value, null),
            $this->rawEngine(Datatype::String->value, Datatype::String->value, null),
            $this->rawEngine(Datatype::String->value, Datatype::String->value, 1),
        ]));
        $this->assertTrue(DatatypeService::traceBack([$sortedTarget])['status']);
    }
}
