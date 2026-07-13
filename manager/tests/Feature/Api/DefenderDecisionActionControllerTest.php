<?php

namespace Tests\Feature\Api;

use App\Enums\Decision\Action as DecisionAction;
use App\Enums\Decision\Condition;
use App\Enums\Decision\Direction;
use App\Enums\Defender\DeploymentStatus;
use App\Jobs\DefenderCommunication;
use App\Models\Decision;
use App\Models\Guard;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Tests\Support\ApiRelationTestHelpers;

class DefenderDecisionActionControllerTest extends ApiTestCase
{
    use ApiRelationTestHelpers;

    public function test_defender_decision_actions_dispatch_jobs_only_when_actionable(): void
    {
        Bus::fake();

        $defender = $this->apiDefender('actionable', DeploymentStatus::Successful->value);
        $decision = Decision::query()->create([
            'name' => 'decision-'.Str::lower(Str::random(6)),
            'direction' => Direction::Request->value,
            'condition' => Condition::GreaterThanOrEqual->value,
            'score' => 5,
            'action' => DecisionAction::Allow->value,
        ]);

        $defender->decisions()->attach($decision->id, ['order' => 1, 'is_implemented' => false]);

        $this->apiJson('POST', $this->apiRoute('defenders.decisions', 'implement'), [
            'defender' => $defender->id,
            'decision' => $decision->id,
        ])->assertOk()->assertJsonPath('id', $decision->id);

        Bus::assertDispatched(DefenderCommunication::class, fn (DefenderCommunication $job) => $job->action === DefenderCommunication::ACTION_IMPLEMENT);

        $defender->decisions()->updateExistingPivot($decision->id, ['is_implemented' => true]);
        $this->apiJson('POST', $this->apiRoute('defenders.decisions', 'suspend'), [
            'defender' => $defender->id,
            'decision' => $decision->id,
        ])->assertOk()->assertJsonPath('id', $decision->id);

        Bus::assertDispatched(DefenderCommunication::class, fn (DefenderCommunication $job) => $job->action === DefenderCommunication::ACTION_SUSPEND);
    }

    public function test_defender_decision_actions_skip_unattached_or_inactive_records(): void
    {
        Bus::fake();

        $failedDefender = $this->apiDefender('inactive-actions', DeploymentStatus::Failed->value);
        $successfulDefender = $this->apiDefender('attached-actions', DeploymentStatus::Successful->value);
        $decision = Decision::query()->create([
            'name' => 'skipped-decision-'.Str::lower(Str::random(6)),
            'direction' => Direction::Request->value,
            'condition' => Condition::GreaterThanOrEqual->value,
            'score' => 5,
            'action' => DecisionAction::Allow->value,
        ]);

        $successfulDefender->decisions()->attach($decision->id, ['order' => 1, 'is_implemented' => false]);

        $this->apiJson('POST', $this->apiRoute('defenders.decisions', 'implement'), [
            'defender' => $failedDefender->id,
            'decision' => $decision->id,
        ])->assertOk()->assertJsonPath('id', $decision->id);

        $this->apiJson('POST', $this->apiRoute('defenders.decisions', 'suspend'), [
            'defender' => $successfulDefender->id,
            'decision' => $decision->id,
        ])->assertOk()->assertJsonPath('id', $decision->id);

        Bus::assertNotDispatched(DefenderCommunication::class);
    }

    public function test_defender_decision_actions_require_active_matching_guard(): void
    {
        Bus::fake();

        $defender = $this->apiDefender('guarded-decision', DeploymentStatus::Successful->value);
        $decision = Decision::query()->create([
            'name' => 'guarded-decision-'.Str::lower(Str::random(6)),
            'direction' => Direction::Request->value,
            'condition' => Condition::GreaterThanOrEqual->value,
            'score' => 5,
            'action' => DecisionAction::Allow->value,
        ]);
        $defender->decisions()->attach($decision->id, ['order' => 1, 'is_implemented' => false]);

        $guard = Guard::query()->create([
            'name' => 'decision-guard',
            'expired_at' => now()->addHour(),
        ]);
        $guard->defenders()->attach($defender->id);

        $this->apiJson('POST', $this->apiRoute('defenders.decisions', 'implement'), [
            'defender' => $defender->id,
            'decision' => $decision->id,
        ])->assertForbidden();

        $guard->users()->attach($this->user->id);

        $this->apiJson('POST', $this->apiRoute('defenders.decisions', 'implement'), [
            'defender' => $defender->id,
            'decision' => $decision->id,
        ])->assertOk();

        Bus::assertDispatched(DefenderCommunication::class);
    }
}
