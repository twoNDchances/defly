<?php

namespace Tests\Feature\Gui\Widgets;

use App\Enums\Action\Type as ActionType;
use App\Enums\Decision\Action as DecisionAction;
use App\Enums\Decision\Condition;
use App\Enums\Decision\Direction;
use App\Enums\Defender\Status;
use App\Models\Action;
use App\Models\Decision;
use App\Models\Defender;
use App\Models\Report;
use App\Models\Timeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\FilamentTestHelpers;
use Tests\Support\RegistersSqliteFunctions;
use Tests\TestCase;

class FilamentWidgetPayloadTest extends TestCase
{
    use FilamentTestHelpers;
    use RefreshDatabase;
    use RegistersSqliteFunctions;

    public function test_filament_widget_classes_return_stats_and_chart_payloads(): void
    {
        $this->registerSqliteJsonUnquoteFunction();

        $defender = $this->filamentDefender();
        $defender->forceFill([
            'status' => Status::Abnormal->value,
            'details' => ['reasons' => ['high error rate'], 'process_memory_sys_mib' => 32, 'goroutines' => 4],
        ])->save();
        $action = Action::query()->create(['name' => 'widget-action-'.Str::lower(Str::random(6)), 'type' => ActionType::Report->value]);
        $principle = $this->filamentPrinciple();
        $decision = Decision::query()->create([
            'name' => 'widget-decision-'.Str::lower(Str::random(6)),
            'direction' => Direction::Request->value,
            'condition' => Condition::GreaterThanOrEqual->value,
            'score' => 5,
            'action' => DecisionAction::Allow->value,
        ]);
        $defender->principles()->attach($principle->id, ['order' => 1, 'is_applied' => true]);
        $defender->decisions()->attach($decision->id, ['order' => 1, 'is_implemented' => true]);
        Report::withoutEvents(fn () => Report::query()->create([
            'metas' => ['ip' => '10.0.0.1', 'method' => 'post', 'status' => 418],
            'triggered_by' => $action->id,
            'created_by' => $defender->id,
        ]));
        Timeline::withoutEvents(fn () => Timeline::query()->create([
            'action' => 'deploy',
            'resource_type' => Defender::class,
            'resource_id' => $defender->id,
        ]));

        foreach ($this->classesUnder(app_path('Filament/Widgets'), fn (string $file) => str_ends_with($file, '.php')) as $class) {
            if (str_contains($class, '\\Concerns\\')) {
                continue;
            }

            $this->assertWidgetPayloads($class, $defender);
        }

        foreach ($this->classesUnder(app_path('Filament/Clusters/Infrastructure/Resources/Defenders/Widgets'), fn (string $file) => str_ends_with($file, '.php')) as $class) {
            $this->assertWidgetPayloads($class, $defender);
        }
    }
}
