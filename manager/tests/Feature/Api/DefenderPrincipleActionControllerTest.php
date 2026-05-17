<?php

namespace Tests\Feature\Api;

use App\Enums\Defender\DeploymentStatus;
use App\Enums\Phase;
use App\Enums\Principle\ValidationStatus;
use App\Jobs\DefenderCommunication;
use App\Models\Principle;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Tests\Support\ApiRelationTestHelpers;

class DefenderPrincipleActionControllerTest extends ApiTestCase
{
    use ApiRelationTestHelpers;

    public function test_defender_principle_actions_dispatch_jobs_only_when_actionable(): void
    {
        Bus::fake();

        $defender = $this->apiDefender('actionable', DeploymentStatus::Successful->value);
        $principle = Principle::query()->create([
            'name' => 'principle-'.Str::lower(Str::random(6)),
            'level' => 1,
            'phase' => Phase::One->value,
            'validation_status' => ValidationStatus::Passed->value,
        ]);

        $defender->principles()->attach($principle->id, ['order' => 1, 'is_applied' => false]);

        $this->apiJson('POST', $this->apiRoute('defenders.principles', 'apply'), [
            'defender' => $defender->id,
            'principle' => $principle->id,
        ])->assertOk()->assertJsonPath('id', $principle->id);

        Bus::assertDispatched(DefenderCommunication::class, fn (DefenderCommunication $job) => $job->action === DefenderCommunication::ACTION_APPLY);

        $defender->principles()->updateExistingPivot($principle->id, ['is_applied' => true]);
        $this->apiJson('POST', $this->apiRoute('defenders.principles', 'revoke'), [
            'defender' => $defender->id,
            'principle' => $principle->id,
        ])->assertOk()->assertJsonPath('id', $principle->id);

        Bus::assertDispatched(DefenderCommunication::class, fn (DefenderCommunication $job) => $job->action === DefenderCommunication::ACTION_REVOKE);
    }

    public function test_defender_principle_actions_skip_unattached_or_inactive_records(): void
    {
        Bus::fake();

        $failedDefender = $this->apiDefender('inactive-actions', DeploymentStatus::Failed->value);
        $successfulDefender = $this->apiDefender('attached-actions', DeploymentStatus::Successful->value);
        $principle = Principle::query()->create([
            'name' => 'skipped-principle-'.Str::lower(Str::random(6)),
            'level' => 1,
            'phase' => Phase::One->value,
            'validation_status' => ValidationStatus::Failed->value,
        ]);
        $passedPrinciple = Principle::query()->create([
            'name' => 'unapplied-principle-'.Str::lower(Str::random(6)),
            'level' => 1,
            'phase' => Phase::One->value,
            'validation_status' => ValidationStatus::Passed->value,
        ]);

        $successfulDefender->principles()->attach($passedPrinciple->id, ['order' => 1, 'is_applied' => false]);

        $this->apiJson('POST', $this->apiRoute('defenders.principles', 'apply'), [
            'defender' => $failedDefender->id,
            'principle' => $principle->id,
        ])->assertOk()->assertJsonPath('id', $principle->id);

        $this->apiJson('POST', $this->apiRoute('defenders.principles', 'revoke'), [
            'defender' => $successfulDefender->id,
            'principle' => $passedPrinciple->id,
        ])->assertOk()->assertJsonPath('id', $passedPrinciple->id);

        Bus::assertNotDispatched(DefenderCommunication::class);
    }
}
