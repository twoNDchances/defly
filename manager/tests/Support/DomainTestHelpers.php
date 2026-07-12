<?php

namespace Tests\Support;

use App\Enums\Action\Type as ActionType;
use App\Enums\Decision\Action as DecisionAction;
use App\Enums\Decision\Condition;
use App\Enums\Decision\Direction;
use App\Enums\Engine\Type as EngineType;
use App\Enums\Phase;
use App\Enums\Rule\Comparator;
use App\Enums\Type as TargetType;
use App\Enums\Wordlist\Type as WordlistType;
use App\Models\Action;
use App\Models\Decision;
use App\Models\Defender;
use App\Models\Engine;
use App\Models\Permission;
use App\Models\Principle;
use App\Models\Rule;
use App\Models\Target;
use App\Models\Wordlist;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;
use ReflectionMethod;

trait DomainTestHelpers
{
    protected function invokeJob(object $job, string $method, mixed ...$arguments): mixed
    {
        $reflection = new ReflectionMethod($job, $method);

        return $reflection->invoke($job, ...$arguments);
    }

    protected function invokeJobWithReferences(object $job, string $method, array $arguments): mixed
    {
        $reflection = new ReflectionMethod($job, $method);

        return $reflection->invokeArgs($job, $arguments);
    }

    protected function invokeStatic(string $class, string $method, mixed ...$arguments): mixed
    {
        $reflection = new ReflectionMethod($class, $method);

        return $reflection->invoke(null, ...$arguments);
    }

    protected function permission(string $name, string $appliedFor, string $action): Permission
    {
        return Permission::query()->create([
            'name' => $name.'-'.Str::lower(Str::random(6)),
            'applied_for' => $appliedFor,
            'action' => $action,
        ]);
    }

    protected function wordlist(): Wordlist
    {
        return Wordlist::query()->create([
            'name' => 'wordlist-'.Str::lower(Str::random(6)),
            'type' => WordlistType::Json->value,
            'word_json' => [['word' => 'alpha'], ['word' => 'beta']],
        ]);
    }

    protected function target(string $datatype): Target
    {
        return Target::query()->create([
            'name' => 'target-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'type' => TargetType::Getter->value,
            'datatype' => $datatype,
        ]);
    }

    protected function engine(string $input, string $type, string $output): Engine
    {
        return Engine::query()->create([
            'name' => 'engine-'.Str::lower(Str::random(6)),
            'input_datatype' => $input,
            'type' => $type,
            'configurations' => ['digit' => 1],
            'output_datatype' => $output,
        ]);
    }

    protected function rawEngine(?string $input, ?string $output, mixed $order = null): Engine
    {
        $engine = new Engine;
        $engine->setRawAttributes([
            'id' => (string) Str::uuid(),
            'name' => 'raw-engine-'.Str::lower(Str::random(6)),
            'input_datatype' => $input,
            'type' => EngineType::Lower->value,
            'output_datatype' => $output,
        ], true);

        $pivot = new Pivot;
        $pivot->setRawAttributes(['order' => $order], true);
        $engine->setRelation('pivot', $pivot);

        return $engine;
    }

    protected function rule(Target $target): Rule
    {
        return Rule::query()->create([
            'name' => 'rule-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'target_id' => $target->id,
            'comparator' => Comparator::Mirror->value,
            'is_inversed' => false,
            'configurations' => ['string' => 'needle'],
        ]);
    }

    protected function action(string $type = ActionType::Allow->value): Action
    {
        return Action::query()->create([
            'name' => 'action-'.Str::lower(Str::random(6)),
            'type' => $type,
        ]);
    }

    protected function principle(): Principle
    {
        return Principle::query()->create([
            'name' => 'principle-'.Str::lower(Str::random(6)),
            'level' => 1,
            'phase' => Phase::One->value,
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
        ]);
    }

    protected function defender(string $name, ?string $deploymentStatus, array $environment = []): Defender
    {
        return Defender::query()->create([
            'name' => $name,
            'proxy_port' => 9948,
            'deployment_status' => $deploymentStatus,
            'environment_variables' => [
                'SERVER_HTTPS_ENABLE' => 'false',
                'SERVER_PORT' => '9947',
                'SERVER_CONTROLLER_PATH_PREFIX' => 'api/v1',
                'PROXY_BACKEND_URL' => 'http://localhost',
                ...$environment,
            ],
        ]);
    }
}
