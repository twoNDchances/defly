<?php

namespace Tests\Feature\Policies;

use App\Enums\Datatype;
use App\Enums\Phase;
use App\Enums\Principle\ValidationStatus;
use App\Enums\Type as TargetType;
use App\Models\Pattern;
use App\Models\Report;
use App\Models\User;
use App\Policies\PatternPolicy;
use App\Policies\PrinciplePolicy;
use App\Policies\ReportPolicy;
use App\Policies\TimelinePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\ModelTestHelpers;
use Tests\TestCase;

class PrincipleReportPatternTimelinePolicyTest extends TestCase
{
    use ModelTestHelpers;
    use RefreshDatabase;

    public function test_remaining_resource_policies_block_or_allow_expected_actions(): void
    {
        /** @var User $root */
        $root = User::factory()->create(['is_root' => true, 'is_verified' => true, 'is_activated' => true]);
        $this->actingAs($root);

        $principlePolicy = new PrinciplePolicy;
        $validatingPrinciple = $this->modelPrinciple(ValidationStatus::Validating->value);
        $passedPrinciple = $this->modelPrinciple(ValidationStatus::Passed->value);
        $this->assertFalse($principlePolicy->validate($root, $validatingPrinciple));
        $this->assertTrue($principlePolicy->apply($root, $passedPrinciple));
        $this->assertTrue($principlePolicy->clone($root, $passedPrinciple));

        $reportPolicy = new ReportPolicy;
        $report = Report::withoutEvents(fn () => Report::query()->create(['is_reviewed' => false]));
        $reviewedReport = Report::withoutEvents(fn () => Report::query()->create(['is_reviewed' => true]));
        $this->assertTrue($reportPolicy->review($root, $report));
        $this->assertFalse($reportPolicy->review($root, $reviewedReport));
        $this->assertFalse($reportPolicy->create($root));

        $pattern = Pattern::query()->create([
            'name' => 'policy-pattern-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'type' => TargetType::Full->value,
            'datatype' => Datatype::String->value,
        ]);
        $this->assertFalse((new PatternPolicy)->create($root));
        $this->assertFalse((new PatternPolicy)->update($root, $pattern));
        $this->assertFalse((new PatternPolicy)->deleteAny($root));
        $this->assertFalse((new PatternPolicy)->delete($root, $pattern));
        $this->assertFalse((new TimelinePolicy)->create($root));
    }
}
