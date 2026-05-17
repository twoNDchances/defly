<?php

namespace Tests\Feature\Models;

use App\Enums\Action\Type as ActionType;
use App\Enums\Datatype;
use App\Enums\Decision\Action as DecisionAction;
use App\Enums\Defender\DeploymentStatus;
use App\Enums\Phase;
use App\Enums\Principle\ValidationStatus;
use App\Enums\Rule\Comparator;
use App\Enums\Type as TargetType;
use App\Enums\Wordlist\Type as WordlistType;
use App\Models\Group;
use App\Models\Label;
use App\Models\Pattern;
use App\Models\Permission;
use App\Models\Report;
use App\Models\Target;
use App\Models\Timeline;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\ModelTestHelpers;
use Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    use ModelTestHelpers;
    use RefreshDatabase;

    public function test_model_casts_and_relationships_are_wired(): void
    {
        $label = Label::query()->create(['name' => 'label-'.Str::lower(Str::random(6)), 'color' => '#ffffff']);
        $group = Group::query()->create(['name' => 'group-'.Str::lower(Str::random(6))]);
        $permission = Permission::query()->create([
            'name' => 'permission-'.Str::lower(Str::random(6)),
            'applied_for' => 'Action',
            'action' => 'viewAny',
        ]);
        $wordlist = $this->modelWordlist();
        $pattern = Pattern::query()->create([
            'name' => 'pattern-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'type' => TargetType::Full->value,
            'datatype' => Datatype::String->value,
        ]);
        $target = Target::query()->create([
            'name' => 'target-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'type' => TargetType::Full->value,
            'datatype' => Datatype::String->value,
            'pattern_id' => $pattern->id,
        ]);
        $engine = $this->modelEngine();
        $target->engines()->attach($engine->id, ['order' => 1]);
        $action = $this->modelAction();
        $rule = $this->modelRule($target);
        $rule->actions()->attach($action->id, ['order' => 1]);
        $principle = $this->modelPrinciple();
        $principle->rules()->attach($rule->id, ['order' => 1]);
        $decision = $this->modelDecision();
        $defender = $this->modelDefender();
        $defender->principles()->attach($principle->id, ['order' => 1, 'is_applied' => false]);
        $defender->decisions()->attach($decision->id, ['order' => 1, 'is_implemented' => false]);
        $report = Report::withoutEvents(fn () => Report::query()->create([
            'metas' => ['ip' => '127.0.0.1'],
            'triggered_by' => $action->id,
            'created_by' => $defender->id,
        ]));
        $timeline = Timeline::withoutEvents(fn () => Timeline::query()->create([
            'action' => 'view',
            'resource_type' => $action->getMorphClass(),
            'resource_id' => $action->id,
        ]));

        $label->actions()->attach($action->id);
        $group->permissions()->attach($permission->id);

        $this->assertInstanceOf(WordlistType::class, $wordlist->type);
        $this->assertInstanceOf(TargetType::class, $target->type);
        $this->assertInstanceOf(Datatype::class, $target->datatype);
        $this->assertInstanceOf(ActionType::class, $action->type);
        $this->assertInstanceOf(Comparator::class, $rule->comparator);
        $this->assertInstanceOf(ValidationStatus::class, $principle->validation_status);
        $this->assertInstanceOf(DecisionAction::class, $decision->action);
        $this->assertInstanceOf(DeploymentStatus::class, $defender->deployment_status);

        $this->assertInstanceOf(MorphToMany::class, $label->actions());
        $this->assertInstanceOf(BelongsToMany::class, $group->permissions());
        $this->assertInstanceOf(BelongsToMany::class, $group->keys());
        $this->assertInstanceOf(MorphToMany::class, $action->labels());
        $this->assertInstanceOf(HasMany::class, $action->reports());
        $this->assertInstanceOf(BelongsTo::class, $rule->target());
        $this->assertInstanceOf(BelongsTo::class, $rule->wordlist());
        $this->assertInstanceOf(HasMany::class, $wordlist->targets());
        $this->assertInstanceOf(HasMany::class, $wordlist->rules());
        $this->assertInstanceOf(BelongsToMany::class, $permission->keys());
        $this->assertInstanceOf(HasMany::class, $pattern->targets());
        $this->assertInstanceOf(MorphMany::class, $action->timelines());
        $this->assertInstanceOf(MorphTo::class, $timeline->resource());
        $this->assertInstanceOf(BelongsTo::class, $report->triggeredBy());
        $this->assertInstanceOf(BelongsTo::class, $report->createdBy());

        $this->assertTrue($action->labels()->whereKey($label->id)->exists());
        $this->assertTrue($rule->actions()->whereKey($action->id)->exists());
        $this->assertTrue($principle->rules()->whereKey($rule->id)->exists());
        $this->assertTrue($defender->reports()->whereKey($report->id)->exists());
    }
}
