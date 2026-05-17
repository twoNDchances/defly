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
use App\Models\Action;
use App\Models\Decision;
use App\Models\Defender;
use App\Models\Engine;
use App\Models\Group;
use App\Models\Label;
use App\Models\Permission;
use App\Models\Principle;
use App\Models\Rule;
use App\Models\Target;
use App\Models\Wordlist;
use Illuminate\Support\Str;

trait ApiRelationTestHelpers
{
    protected function attachListDetach(string $routePrefix, array $params, string $relatedId): void
    {
        $this->apiJson('GET', $this->apiRoute($routePrefix, 'index'), $params)->assertOk();

        $this->apiJson('POST', $this->apiRoute($routePrefix, 'attach'), $params, [
            'ids' => [$relatedId],
        ])->assertOk()->assertJsonFragment(['id' => $relatedId]);

        $this->apiJson('DELETE', $this->apiRoute($routePrefix, 'detach'), $params, [
            'ids' => [$relatedId],
        ])->assertOk()->assertJsonMissing(['id' => $relatedId]);
    }

    protected function label(string $name): Label
    {
        return Label::query()->create([
            'name' => $name.'-'.Str::lower(Str::random(6)),
            'color' => '#0ea5e9',
            'description' => 'Test label',
        ]);
    }

    protected function group(string $name): Group
    {
        return Group::query()->create([
            'name' => $name.'-'.Str::lower(Str::random(6)),
            'description' => 'Test group',
        ]);
    }

    protected function permission(string $name, string $appliedFor, string $action): Permission
    {
        return Permission::query()->create([
            'name' => $name.'-'.Str::lower(Str::random(6)),
            'applied_for' => $appliedFor,
            'action' => $action,
            'description' => 'Test permission',
        ]);
    }

    protected function wordlist(): Wordlist
    {
        return Wordlist::query()->create([
            'name' => 'wordlist-'.Str::lower(Str::random(6)),
            'type' => 'json',
            'word_json' => [['word' => 'admin'], ['word' => 'debug']],
            'description' => 'Test wordlist',
        ]);
    }

    protected function engine(): Engine
    {
        return Engine::query()->create([
            'name' => 'engine-'.Str::lower(Str::random(6)),
            'input_datatype' => Datatype::String->value,
            'type' => EngineType::Lower->value,
            'output_datatype' => Datatype::String->value,
            'description' => 'Test engine',
        ]);
    }

    protected function target(): Target
    {
        return Target::query()->create([
            'name' => 'target-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'type' => TargetType::Getter->value,
            'datatype' => Datatype::String->value,
            'description' => 'Test target',
        ]);
    }

    protected function action(): Action
    {
        return Action::query()->create([
            'name' => 'action-'.Str::lower(Str::random(6)),
            'type' => ActionType::Allow->value,
            'description' => 'Test action',
        ]);
    }

    protected function rule(?Target $target = null): Rule
    {
        $target ??= $this->target();

        return Rule::query()->create([
            'name' => 'rule-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'target_id' => $target->id,
            'comparator' => Comparator::Mirror->value,
            'is_inversed' => false,
            'configurations' => ['string' => 'needle'],
            'description' => 'Test rule',
        ]);
    }

    protected function principle(): Principle
    {
        return Principle::query()->create([
            'name' => 'principle-'.Str::lower(Str::random(6)),
            'level' => 1,
            'phase' => Phase::One->value,
            'validation_status' => ValidationStatus::Passed->value,
            'description' => 'Test principle',
        ]);
    }

    protected function decision(): Decision
    {
        return Decision::query()->create([
            'name' => 'decision-'.Str::lower(Str::random(6)),
            'direction' => Direction::Request->value,
            'condition' => Condition::GreaterThanOrEqual->value,
            'score' => 5,
            'action' => DecisionAction::Allow->value,
            'description' => 'Test decision',
        ]);
    }

    protected function apiDefender(string $name = 'defender', ?string $deploymentStatus = DeploymentStatus::Successful->value): Defender
    {
        return Defender::query()->create([
            'name' => $name.'-'.Str::lower(Str::random(6)),
            'proxy_port' => 9948,
            'deployment_status' => $deploymentStatus,
            'environment_variables' => [
                'SERVER_HTTPS_ENABLE' => 'false',
                'SERVER_PORT' => '9947',
                'SERVER_CONTROLLER_PATH_PREFIX' => 'api/v1',
                'PROXY_BACKEND_URL' => 'http://localhost',
            ],
            'description' => 'Test defender',
        ]);
    }
}
