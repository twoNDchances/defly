<?php

namespace Tests\Feature\Services;

use App\Enums\Action\Type as ActionType;
use App\Enums\Datatype;
use App\Enums\Phase;
use App\Enums\Type as TargetType;
use App\Models\Action;
use App\Models\Target;
use App\Models\User;
use App\Services\Lock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\DomainTestHelpers;
use Tests\TestCase;

class LockServiceTest extends TestCase
{
    use DomainTestHelpers;
    use RefreshDatabase;

    public function test_lock_service_covers_reference_edge_branches(): void
    {
        Lock::syncByForeignKey(new User());
        Lock::syncByDeleting(new User());

        $this->invokeStatic(Lock::class, 'syncById', Target::class, '');
        $this->invokeStatic(Lock::class, 'syncById', Target::class, (string) Str::uuid());
        $this->assertFalse($this->invokeStatic(Lock::class, 'isReferenced', User::factory()->make()));

        $action = $this->action(ActionType::Allow->value);
        $rule = $this->rule($this->target(Datatype::String->value));
        $rule->actions()->attach($action->id, ['order' => 1]);
        $tableUsage = ['type' => 'table', 'table' => 'rules_actions', 'foreign_key' => 'action'];

        $this->assertTrue($this->invokeStatic(Lock::class, 'hasUsage', $tableUsage, $action->id, [
            'pivots' => [['table' => 'other_table', 'self_key' => 'rule', 'self_id' => $rule->id]],
        ]));
        $this->assertTrue($this->invokeStatic(Lock::class, 'hasUsage', $tableUsage, $action->id, [
            'pivots' => [['table' => 'rules_actions', 'self_key' => '', 'self_id' => null]],
        ]));
        $this->assertFalse($this->invokeStatic(Lock::class, 'hasUsage', $tableUsage, $action->id, [
            'pivots' => [['table' => 'rules_actions', 'self_key' => 'rule', 'self_id' => $rule->id]],
        ]));

        Lock::syncByRelationship(Action::class, $action);
        Lock::syncByRelationship(Action::class, collect([$action->id]));
        $this->assertTrue($action->fresh()->is_locked);
    }

    public function test_lock_service_updates_foreign_key_and_pivot_dependents(): void
    {
        $wordlist = $this->wordlist();
        $arrayTarget = Target::query()->create([
            'name' => 'array-target-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'type' => TargetType::Getter->value,
            'datatype' => Datatype::Array->value,
            'wordlist_id' => $wordlist->id,
        ]);
        $this->assertTrue($wordlist->fresh()->is_locked);

        $arrayTarget->delete();
        $this->assertFalse($wordlist->fresh()->is_locked);

        $target = $this->target(Datatype::String->value);
        $rule = $this->rule($target);
        $this->assertTrue($target->fresh()->is_locked);

        $action = $this->action(ActionType::Allow->value);
        $rule->actions()->attach($action->id, ['order' => 1]);
        Lock::syncByRelationship(Action::class, $action->id);
        $this->assertTrue($action->fresh()->is_locked);

        $rule->actions()->detach($action->id);
        Lock::syncByRelationship(Action::class, $action->id);
        $this->assertFalse($action->fresh()->is_locked);
    }
}
