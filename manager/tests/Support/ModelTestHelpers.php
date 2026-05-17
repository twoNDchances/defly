<?php

namespace Tests\Support;

use App\Enums\Action\Type as ActionType;
use App\Enums\Datatype;
use App\Enums\Decision\Action as DecisionAction;
use App\Enums\Decision\Condition;
use App\Enums\Decision\Direction;
use App\Enums\Defender\DeploymentStatus;
use App\Enums\Defender\Status as DefenderStatus;
use App\Enums\Engine\Hash as EngineHash;
use App\Enums\Engine\Type as EngineType;
use App\Enums\Method;
use App\Enums\Phase;
use App\Enums\Principle\ValidationStatus;
use App\Enums\Rule\Comparator;
use App\Enums\Type as TargetType;
use App\Enums\Wordlist\Type as WordlistType;
use App\Models\Action;
use App\Models\Decision;
use App\Models\Defender;
use App\Models\Engine;
use App\Models\Principle;
use App\Models\Rule;
use App\Models\Target;
use App\Models\Wordlist;
use Illuminate\Support\Str;

trait ModelTestHelpers
{
    protected function enumClasses(): array
    {
        return [
            ActionType::class,
            Datatype::class,
            DecisionAction::class,
            Condition::class,
            Direction::class,
            DeploymentStatus::class,
            DefenderStatus::class,
            EngineHash::class,
            EngineType::class,
            Method::class,
            Phase::class,
            ValidationStatus::class,
            Comparator::class,
            TargetType::class,
            WordlistType::class,
        ];
    }

    protected function modelWordlist(): Wordlist
    {
        return Wordlist::query()->create([
            'name' => 'wordlist-'.Str::lower(Str::random(6)),
            'type' => WordlistType::Json->value,
            'word_json' => [['word' => 'alpha']],
        ]);
    }

    protected function modelEngine(): Engine
    {
        return Engine::query()->create([
            'name' => 'engine-'.Str::lower(Str::random(6)),
            'input_datatype' => Datatype::String->value,
            'type' => EngineType::Lower->value,
            'output_datatype' => Datatype::String->value,
        ]);
    }

    protected function modelAction(): Action
    {
        return Action::query()->create([
            'name' => 'action-'.Str::lower(Str::random(6)),
            'type' => ActionType::Allow->value,
        ]);
    }

    protected function modelRule(Target $target): Rule
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

    protected function modelPrinciple(?string $validationStatus = ValidationStatus::Passed->value): Principle
    {
        return Principle::query()->create([
            'name' => 'principle-'.Str::lower(Str::random(6)),
            'level' => 1,
            'phase' => Phase::One->value,
            'validation_status' => $validationStatus,
        ]);
    }

    protected function modelDecision(): Decision
    {
        return Decision::query()->create([
            'name' => 'decision-'.Str::lower(Str::random(6)),
            'direction' => Direction::Request->value,
            'condition' => Condition::GreaterThanOrEqual->value,
            'score' => 5,
            'action' => DecisionAction::Allow->value,
        ]);
    }

    protected function modelDefender(?string $deploymentStatus = DeploymentStatus::Successful->value): Defender
    {
        return Defender::query()->create([
            'name' => 'defender-'.Str::lower(Str::random(6)),
            'proxy_port' => 9948,
            'deployment_status' => $deploymentStatus,
            'environment_variables' => ['PROXY_BACKEND_URL' => 'http://localhost'],
        ]);
    }
}
