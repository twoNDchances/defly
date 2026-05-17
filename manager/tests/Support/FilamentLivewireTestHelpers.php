<?php

namespace Tests\Support;

use App\Enums\Action\Type as ActionType;
use App\Enums\Datatype;
use App\Enums\Decision\Action as DecisionAction;
use App\Enums\Decision\Condition;
use App\Enums\Decision\Direction;
use App\Enums\Defender\DeploymentStatus;
use App\Enums\Engine\Type as EngineType;
use App\Enums\Phase;
use App\Enums\Principle\ValidationStatus;
use App\Enums\Rule\Comparator;
use App\Enums\Type as TargetType;
use App\Enums\Wordlist\Type as WordlistType;
use App\Filament\Clusters\AccessControl\Resources\Groups\Pages\CreateGroup;
use App\Filament\Clusters\AccessControl\Resources\Groups\Pages\EditGroup;
use App\Filament\Clusters\AccessControl\Resources\Groups\Pages\ListGroups;
use App\Filament\Clusters\AccessControl\Resources\Permissions\Pages\CreatePermission;
use App\Filament\Clusters\AccessControl\Resources\Permissions\Pages\EditPermission;
use App\Filament\Clusters\AccessControl\Resources\Permissions\Pages\ListPermissions;
use App\Filament\Clusters\Authentication\Resources\Keys\Pages\CreateKey;
use App\Filament\Clusters\Authentication\Resources\Keys\Pages\EditKey;
use App\Filament\Clusters\Authentication\Resources\Keys\Pages\ListKeys;
use App\Filament\Clusters\Authentication\Resources\Users\Pages\CreateUser;
use App\Filament\Clusters\Authentication\Resources\Users\Pages\EditUser;
use App\Filament\Clusters\Authentication\Resources\Users\Pages\ListUsers;
use App\Filament\Clusters\Context\Resources\Engines\Pages\CreateEngine;
use App\Filament\Clusters\Context\Resources\Engines\Pages\EditEngine;
use App\Filament\Clusters\Context\Resources\Engines\Pages\ListEngines;
use App\Filament\Clusters\Context\Resources\Patterns\Pages\ListPatterns;
use App\Filament\Clusters\Context\Resources\Targets\Pages\CreateTarget;
use App\Filament\Clusters\Context\Resources\Targets\Pages\EditTarget;
use App\Filament\Clusters\Context\Resources\Targets\Pages\ListTargets;
use App\Filament\Clusters\Initialization\Resources\Actions\Pages\CreateAction;
use App\Filament\Clusters\Initialization\Resources\Actions\Pages\EditAction;
use App\Filament\Clusters\Initialization\Resources\Actions\Pages\ListActions;
use App\Filament\Clusters\Initialization\Resources\Decisions\Pages\CreateDecision;
use App\Filament\Clusters\Initialization\Resources\Decisions\Pages\EditDecision;
use App\Filament\Clusters\Initialization\Resources\Decisions\Pages\ListDecisions;
use App\Filament\Clusters\Initialization\Resources\Principles\Pages\CreatePrinciple;
use App\Filament\Clusters\Initialization\Resources\Principles\Pages\EditPrinciple;
use App\Filament\Clusters\Initialization\Resources\Principles\Pages\ListPrinciples;
use App\Filament\Clusters\Initialization\Resources\Rules\Pages\CreateRule;
use App\Filament\Clusters\Initialization\Resources\Rules\Pages\EditRule;
use App\Filament\Clusters\Initialization\Resources\Rules\Pages\ListRules;
use App\Filament\Resources\Defenders\Pages\CreateDefender;
use App\Filament\Resources\Defenders\Pages\EditDefender;
use App\Filament\Resources\Defenders\Pages\ListDefenders;
use App\Filament\Resources\Labels\Pages\CreateLabel;
use App\Filament\Resources\Labels\Pages\EditLabel;
use App\Filament\Resources\Labels\Pages\ListLabels;
use App\Filament\Resources\Timelines\Pages\ListTimelines;
use App\Filament\Resources\Wordlists\Pages\CreateWordlist;
use App\Filament\Resources\Wordlists\Pages\EditWordlist;
use App\Filament\Resources\Wordlists\Pages\ListWordlists;
use App\Models\Action;
use App\Models\Decision;
use App\Models\Defender;
use App\Models\Engine;
use App\Models\Group;
use App\Models\Key;
use App\Models\Label;
use App\Models\Pattern;
use App\Models\Permission;
use App\Models\Principle;
use App\Models\Report;
use App\Models\Rule;
use App\Models\Target;
use App\Models\Timeline;
use App\Models\User;
use App\Models\Wordlist;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

trait FilamentLivewireTestHelpers
{
    use RegistersSqliteFunctions;

    protected User $root;

    protected function setUpFilamentLivewire(): void
    {
        Storage::fake('local');
        $this->registerSqliteJsonUnquoteFunction();

        $this->root = User::factory()->create([
            'is_root' => true,
            'is_verified' => true,
            'is_activated' => true,
        ]);

        $this->actingAs($this->root);
        Filament::setCurrentPanel('defly-manager');
    }

    protected function livewirePage(string $class, array $params = []): \Livewire\Features\SupportTesting\Testable
    {
        try {
            return Livewire::test($class, $params);
        } catch (\Throwable $exception) {
            $this->fail("{$class} failed to render: {$exception->getMessage()}");
        }
    }

    protected function seedRecords(): array
    {
        $user = User::factory()->create([
            'is_root' => false,
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $group = Group::query()->create(['name' => 'group-'.Str::lower(Str::random(6))]);
        $permission = Permission::query()->create([
            'name' => 'permission-'.Str::lower(Str::random(6)),
            'applied_for' => 'Action',
            'action' => 'viewAny',
        ]);
        $key = Key::withoutEvents(fn () => Key::query()->create([
            'name' => 'key-'.Str::lower(Str::random(6)),
            'token' => 'token-'.Str::random(16),
            'is_reused' => false,
            'created_by' => $this->root->id,
        ]));
        $label = Label::query()->create(['name' => 'label-'.Str::lower(Str::random(6)), 'color' => '#2255aa']);
        $wordlist = Wordlist::query()->create([
            'name' => 'wordlist-'.Str::lower(Str::random(6)),
            'type' => WordlistType::Json->value,
            'word_json' => [['word' => 'alpha']],
        ]);
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
        $engine = Engine::query()->create([
            'name' => 'engine-'.Str::lower(Str::random(6)),
            'input_datatype' => Datatype::String->value,
            'type' => EngineType::Lower->value,
            'output_datatype' => Datatype::String->value,
        ]);
        $target->engines()->sync([$engine->id => ['order' => 1]]);
        $action = Action::query()->create([
            'name' => 'action-'.Str::lower(Str::random(6)),
            'type' => ActionType::Allow->value,
        ]);
        $linkedTarget = Target::query()->create([
            'name' => 'linked-target-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'type' => TargetType::Full->value,
            'datatype' => Datatype::String->value,
            'pattern_id' => $pattern->id,
        ]);
        $linkedAction = Action::query()->create([
            'name' => 'linked-action-'.Str::lower(Str::random(6)),
            'type' => ActionType::Allow->value,
        ]);
        $linkedRule = Rule::query()->create([
            'name' => 'linked-rule-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'target_id' => $linkedTarget->id,
            'comparator' => Comparator::Mirror->value,
            'is_inversed' => false,
            'configurations' => ['string' => 'needle'],
        ]);
        $linkedRule->actions()->sync([$linkedAction->id => ['order' => 1]]);
        $ruleTarget = Target::query()->create([
            'name' => 'rule-target-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'type' => TargetType::Full->value,
            'datatype' => Datatype::String->value,
            'pattern_id' => $pattern->id,
        ]);
        $rule = Rule::query()->create([
            'name' => 'rule-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'target_id' => $ruleTarget->id,
            'comparator' => Comparator::Mirror->value,
            'is_inversed' => false,
            'configurations' => ['string' => 'needle'],
        ]);
        $principle = Principle::query()->create([
            'name' => 'principle-'.Str::lower(Str::random(6)),
            'level' => 1,
            'phase' => Phase::One->value,
            'validation_status' => ValidationStatus::Passed->value,
        ]);
        $principle->rules()->sync([$linkedRule->id => ['order' => 1]]);
        $decision = Decision::query()->create([
            'name' => 'decision-'.Str::lower(Str::random(6)),
            'direction' => Direction::Request->value,
            'condition' => Condition::GreaterThanOrEqual->value,
            'score' => 5,
            'action' => DecisionAction::Allow->value,
        ]);
        $defender = Defender::query()->create([
            'name' => 'defender-'.Str::lower(Str::random(6)),
            'proxy_port' => 9844,
            'status' => \App\Enums\Defender\Status::Normal->value,
            'deployment_status' => DeploymentStatus::Successful->value,
            'environment_variables' => ['PROXY_BACKEND_URL' => 'http://localhost'],
        ]);
        $defender->principles()->sync([$principle->id => ['order' => 1, 'is_applied' => true]]);
        $defender->decisions()->sync([$decision->id => ['order' => 1, 'is_implemented' => true]]);
        $report = Report::withoutEvents(fn () => Report::query()->create([
            'is_reviewed' => false,
            'metas' => ['ip' => '127.0.0.1', 'method' => 'get', 'status' => 200],
            'triggered_by' => $action->id,
            'created_by' => $defender->id,
        ]));
        $timeline = Timeline::withoutEvents(fn () => Timeline::query()->create([
            'action' => 'view',
            'resource_type' => $action->getMorphClass(),
            'resource_id' => $action->id,
        ]));

        $label->users()->attach($user->id);
        $label->permissions()->attach($permission->id);
        $label->groups()->attach($group->id);
        $label->wordlists()->attach($wordlist->id);
        $label->engines()->attach($engine->id);
        $label->targets()->attach($target->id);
        $label->actions()->attach($action->id);
        $label->rules()->attach($rule->id);
        $label->principles()->attach($principle->id);
        $label->decisions()->attach($decision->id);
        $label->defenders()->attach($defender->id);

        $group->users()->attach($user->id);
        $group->permissions()->attach($permission->id);
        $key->groups()->attach($group->id);
        $key->permissions()->attach($permission->id);

        return compact(
            'action',
            'decision',
            'defender',
            'engine',
            'group',
            'key',
            'label',
            'pattern',
            'permission',
            'principle',
            'report',
            'rule',
            'target',
            'timeline',
            'user',
            'wordlist',
        );
    }

    protected function listPages(array $records): array
    {
        return [
            [ListGroups::class, $records['group']],
            [ListPermissions::class, $records['permission']],
            [ListKeys::class, $records['key']],
            [ListUsers::class, $records['user']],
            [ListEngines::class, $records['engine']],
            [ListPatterns::class, $records['pattern']],
            [ListTargets::class, $records['target']],
            [ListActions::class, $records['action']],
            [ListDecisions::class, $records['decision']],
            [ListPrinciples::class, $records['principle']],
            [ListRules::class, $records['rule']],
            [ListDefenders::class, $records['defender']],
            [ListLabels::class, $records['label']],
            [ListTimelines::class, $records['timeline']],
            [ListWordlists::class, $records['wordlist']],
        ];
    }

    protected function createPages(): array
    {
        return [
            CreateGroup::class,
            CreatePermission::class,
            CreateKey::class,
            CreateUser::class,
            CreateEngine::class,
            CreateTarget::class,
            CreateAction::class,
            CreateDecision::class,
            CreatePrinciple::class,
            CreateRule::class,
            CreateDefender::class,
            CreateLabel::class,
            CreateWordlist::class,
        ];
    }

    protected function editPages(array $records): array
    {
        return [
            [EditGroup::class, $records['group']],
            [EditPermission::class, $records['permission']],
            [EditKey::class, $records['key']],
            [EditUser::class, $records['user']],
            [EditEngine::class, $records['engine']],
            [EditTarget::class, $records['target']],
            [EditAction::class, $records['action']],
            [EditDecision::class, $records['decision']],
            [EditPrinciple::class, $records['principle']],
            [EditRule::class, $records['rule']],
            [EditDefender::class, $records['defender']],
            [EditLabel::class, $records['label']],
            [EditWordlist::class, $records['wordlist']],
        ];
    }

    protected function relationManagers(array $records): array
    {
        return [
            [\App\Filament\Clusters\AccessControl\Resources\Groups\RelationManagers\PermissionsRelationManager::class, $records['group'], EditGroup::class],
            [\App\Filament\Clusters\AccessControl\Resources\Groups\RelationManagers\UsersRelationManager::class, $records['group'], EditGroup::class],
            [\App\Filament\Clusters\AccessControl\Resources\Permissions\RelationManagers\GroupsRelationManager::class, $records['permission'], EditPermission::class],
            [\App\Filament\Clusters\AccessControl\Resources\Permissions\RelationManagers\UsersRelationManager::class, $records['permission'], EditPermission::class],
            [\App\Filament\Clusters\Authentication\Resources\Keys\RelationManagers\GroupsRelationManager::class, $records['key'], EditKey::class],
            [\App\Filament\Clusters\Authentication\Resources\Keys\RelationManagers\PermissionsRelationManager::class, $records['key'], EditKey::class],
            [\App\Filament\Clusters\Authentication\Resources\Users\RelationManagers\GroupsRelationManager::class, $records['user'], EditUser::class],
            [\App\Filament\Clusters\Authentication\Resources\Users\RelationManagers\PermissionsRelationManager::class, $records['user'], EditUser::class],
            [\App\Filament\Clusters\Context\Resources\Targets\RelationManagers\EnginesRelationManager::class, $records['target'], EditTarget::class],
            [\App\Filament\Clusters\Initialization\Resources\Principles\RelationManagers\RulesRelationManager::class, $records['principle'], EditPrinciple::class],
            [\App\Filament\Clusters\Initialization\Resources\Rules\RelationManagers\ActionsRelationManager::class, $records['rule'], EditRule::class],
            [\App\Filament\Resources\Defenders\RelationManagers\DecisionsRelationManager::class, $records['defender'], EditDefender::class],
            [\App\Filament\Resources\Defenders\RelationManagers\PrinciplesRelationManager::class, $records['defender'], EditDefender::class],
            [\App\Filament\Resources\Defenders\RelationManagers\ReportsRelationManager::class, $records['defender'], EditDefender::class],
            [\App\Filament\Resources\Labels\RelationManagers\ActionsRelationManager::class, $records['label'], EditLabel::class],
            [\App\Filament\Resources\Labels\RelationManagers\DecisionsRelationManager::class, $records['label'], EditLabel::class],
            [\App\Filament\Resources\Labels\RelationManagers\DefendersRelationManager::class, $records['label'], EditLabel::class],
            [\App\Filament\Resources\Labels\RelationManagers\EnginesRelationManager::class, $records['label'], EditLabel::class],
            [\App\Filament\Resources\Labels\RelationManagers\GroupsRelationManager::class, $records['label'], EditLabel::class],
            [\App\Filament\Resources\Labels\RelationManagers\PermissionsRelationManager::class, $records['label'], EditLabel::class],
            [\App\Filament\Resources\Labels\RelationManagers\PrinciplesRelationManager::class, $records['label'], EditLabel::class],
            [\App\Filament\Resources\Labels\RelationManagers\RulesRelationManager::class, $records['label'], EditLabel::class],
            [\App\Filament\Resources\Labels\RelationManagers\TargetsRelationManager::class, $records['label'], EditLabel::class],
            [\App\Filament\Resources\Labels\RelationManagers\UsersRelationManager::class, $records['label'], EditLabel::class],
            [\App\Filament\Resources\Labels\RelationManagers\WordlistsRelationManager::class, $records['label'], EditLabel::class],
        ];
    }
}
