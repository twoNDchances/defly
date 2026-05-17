<?php

namespace Tests\Feature\Api;

use App\Enums\Action\Type as ActionType;
use App\Enums\Defender\DeploymentStatus;
use App\Models\Action;
use App\Models\Report;
use Illuminate\Support\Str;
use Tests\Support\ApiRelationTestHelpers;

class DefenderReportControllerTest extends ApiTestCase
{
    use ApiRelationTestHelpers;

    public function test_defender_reports_are_scoped_to_the_defender(): void
    {
        $defender = $this->apiDefender('report-owner', DeploymentStatus::Successful->value);
        $otherDefender = $this->apiDefender('other-owner', DeploymentStatus::Successful->value);
        $action = Action::query()->create([
            'name' => 'report-action-'.Str::lower(Str::random(6)),
            'type' => ActionType::Report->value,
        ]);
        $report = Report::withoutEvents(fn () => Report::query()->create([
            'metas' => ['ip' => '127.0.0.1'],
            'request_headers' => ['accept' => 'application/json'],
            'request_body' => ['path' => '/blocked'],
            'response_headers' => ['content-type' => 'application/json'],
            'response_body' => ['blocked' => true],
            'rule_details' => ['rule' => 'matched'],
            'triggered_by' => $action->id,
            'created_by' => $defender->id,
        ]));

        $this->apiJson('GET', $this->apiRoute('defenders.reports', 'index'), ['defender' => $defender->id])
            ->assertOk()
            ->assertJsonFragment(['id' => $report->id]);

        $this->apiJson('GET', $this->apiRoute('defenders.reports', 'show'), [
            'defender' => $defender->id,
            'report' => $report->id,
        ])->assertOk()->assertJsonPath('id', $report->id);

        $this->apiJson('GET', $this->apiRoute('defenders.reports', 'show'), [
            'defender' => $otherDefender->id,
            'report' => $report->id,
        ])->assertForbidden();

        $this->apiJson('DELETE', $this->apiRoute('defenders.reports', 'destroy'), [
            'defender' => $defender->id,
            'report' => $report->id,
        ])->assertNoContent();

        $this->assertDatabaseMissing('reports', ['id' => $report->id]);
    }
}
