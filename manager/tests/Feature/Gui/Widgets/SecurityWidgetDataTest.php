<?php

namespace Tests\Feature\Gui\Widgets;

use App\Enums\Action\Type as ActionType;
use App\Enums\Decision\Action as DecisionAction;
use App\Enums\Decision\Condition;
use App\Enums\Decision\Direction;
use App\Enums\Defender\DeploymentStatus;
use App\Enums\Defender\Status;
use App\Enums\Phase;
use App\Enums\Principle\ValidationStatus;
use App\Models\Action;
use App\Models\Decision;
use App\Models\Defender;
use App\Models\Principle;
use App\Models\Report;
use App\Models\Timeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\WidgetDataHarness;
use Tests\TestCase;

class SecurityWidgetDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_widget_data_helpers_calculate_dashboard_series(): void
    {
        $harness = new WidgetDataHarness;
        $defender = Defender::query()->create([
            'name' => 'widget-defender',
            'proxy_port' => 9948,
            'status' => Status::Normal->value,
            'deployment_status' => DeploymentStatus::Successful->value,
            'environment_variables' => ['PROXY_BACKEND_URL' => 'http://localhost'],
        ]);
        $harness->record = $defender;
        $action = Action::query()->create(['name' => 'widget-action', 'type' => ActionType::Report->value]);
        $principle = Principle::query()->create([
            'name' => 'widget-principle',
            'level' => 1,
            'phase' => Phase::One->value,
            'validation_status' => ValidationStatus::Passed->value,
        ]);
        $decision = Decision::query()->create([
            'name' => 'widget-decision',
            'direction' => Direction::Request->value,
            'condition' => Condition::GreaterThanOrEqual->value,
            'score' => 5,
            'action' => DecisionAction::Allow->value,
        ]);
        $defender->principles()->attach($principle->id, ['order' => 1, 'is_applied' => true]);
        $defender->decisions()->attach($decision->id, ['order' => 1, 'is_implemented' => true]);
        Report::withoutEvents(fn () => Report::query()->create([
            'metas' => ['ip' => '127.0.0.1', 'method' => 'get', 'status' => 403],
            'triggered_by' => $action->id,
            'created_by' => $defender->id,
        ]));
        Timeline::withoutEvents(fn () => Timeline::query()->create([
            'action' => 'deploy',
            'resource_type' => Defender::class,
            'resource_id' => $defender->id,
        ]));

        $this->assertSame($defender->id, $harness->currentDefenderPublic()?->id);
        $this->assertSame(1, $harness->reportsQueryPublic($defender)->count());
        $this->assertSame(1, $harness->timelinesQueryPublic($defender)->count());
        $this->assertSame(1, $harness->countTodayPublic($harness->reportsQueryPublic()));
        $this->assertCount(3, $harness->dateCountSeriesPublic($harness->reportsQueryPublic(), 3)['data']);
        $this->assertSame(1, $harness->topReportJsonValuesPublic('metas', '$.ip', $defender)->get('127.0.0.1'));
        $this->assertSame(1, $harness->uniqueReportJsonCountPublic('metas', '$.ip', $defender));
        $this->assertSame(1, $harness->topTriggeredActionsPublic($defender)->get('widget-action'));
        $this->assertSame(1, $harness->topReportingDefendersPublic()->get('widget-defender'));
        $this->assertSame(1, $harness->groupedDefenderCountsPublic('status')->get(Status::Normal->value));
        $this->assertSame(1, $harness->groupedPrincipleValidationCountsPublic()->get(ValidationStatus::Passed->value));
        $this->assertSame(1, $harness->groupedTimelineActionsPublic($defender)->get('deploy'));
        $this->assertSame(1, $harness->policyCoveragePublic($defender)['principles_applied']);
        $this->assertSame(0, $harness->policyCoveragePublic(null)['principles_total']);
        $this->assertNotEmpty($harness->reportScatterPointsPublic($defender));
        $emptyDefender = Defender::query()->create([
            'name' => 'empty-widget-defender',
            'proxy_port' => 9950,
            'status' => Status::Normal->value,
            'deployment_status' => DeploymentStatus::Successful->value,
            'environment_variables' => ['PROXY_BACKEND_URL' => 'http://localhost'],
        ]);
        $this->assertSame([['x' => 0, 'y' => 0]], $harness->reportScatterPointsPublic($emptyDefender));
        $this->assertSame([0], $harness->valuesOrZeroPublic(collect()));
        $this->assertNotEmpty($harness->labelsOrEmptyPublic(collect()));
        $this->assertSame('1.5K', $harness->formatNumberPublic(1500));
        $this->assertSame('1.5M', $harness->formatNumberPublic(1500000));
        $this->assertCount(8, $harness->chartPalettePublic());
        $this->assertNotEmpty($harness->bubblePointsPublic(collect(['a' => 5])));
    }

    public function test_security_widget_date_filters_limit_report_and_timeline_queries(): void
    {
        $harness = new WidgetDataHarness;
        $defender = Defender::query()->create([
            'name' => 'filtered-widget-defender',
            'proxy_port' => 9951,
            'status' => Status::Normal->value,
            'deployment_status' => DeploymentStatus::Successful->value,
            'environment_variables' => ['PROXY_BACKEND_URL' => 'http://localhost'],
        ]);

        Report::withoutEvents(fn () => Report::query()->create([
            'metas' => ['ip' => '127.0.0.1', 'method' => 'get', 'status' => 200],
            'created_by' => $defender->id,
        ]));
        Timeline::withoutEvents(fn () => Timeline::query()->create([
            'action' => 'deploy',
            'resource_type' => Defender::class,
            'resource_id' => $defender->id,
        ]));

        $oldReport = Report::withoutEvents(fn () => Report::query()->create([
            'metas' => ['ip' => '127.0.0.2', 'method' => 'post', 'status' => 403],
            'created_by' => $defender->id,
        ]));
        $oldReport->forceFill([
            'created_at' => now()->subDays(20),
            'updated_at' => now()->subDays(20),
        ])->saveQuietly();

        $oldTimeline = Timeline::withoutEvents(fn () => Timeline::query()->create([
            'action' => 'cancel',
            'resource_type' => Defender::class,
            'resource_id' => $defender->id,
        ]));
        $oldTimeline->forceFill([
            'created_at' => now()->subDays(20),
            'updated_at' => now()->subDays(20),
        ])->saveQuietly();

        $this->assertArrayHasKey('all', $harness->filtersPublic());

        $harness->filter = '14';
        $this->assertSame(14, $harness->selectedSecurityDateFilterDaysPublic());
        $this->assertSame(1, $harness->filteredReportsQueryPublic($defender)->count());
        $this->assertSame(1, $harness->filteredTimelinesQueryPublic($defender)->count());

        $harness->filter = 'all';
        $this->assertNull($harness->selectedSecurityDateFilterDaysPublic());
        $this->assertSame(2, $harness->filteredReportsQueryPublic($defender)->count());
        $this->assertSame(2, $harness->filteredTimelinesQueryPublic($defender)->count());
    }
}
