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
use App\Enums\Wordlist\Type as WordlistType;
use App\Models\Action;
use App\Models\Decision;
use App\Models\Defender;
use App\Models\Engine;
use App\Models\Principle;
use App\Models\Rule;
use App\Models\Target;
use App\Models\Wordlist;
use App\Traits\Filament\Specifics\Key\KeyData;
use App\Traits\Filament\Specifics\User\UserData;
use App\Traits\Validators\PatternValidator;
use Illuminate\Support\Str;

trait ModelTestHelpers
{
    protected function enumClasses(): array
    {
        return [
            \App\Enums\Action\Type::class,
            \App\Enums\Datatype::class,
            \App\Enums\Decision\Action::class,
            \App\Enums\Decision\Condition::class,
            \App\Enums\Decision\Direction::class,
            \App\Enums\Defender\DeploymentStatus::class,
            \App\Enums\Defender\Status::class,
            \App\Enums\Engine\Hash::class,
            \App\Enums\Engine\Type::class,
            \App\Enums\Method::class,
            \App\Enums\Phase::class,
            \App\Enums\Principle\ValidationStatus::class,
            \App\Enums\Rule\Comparator::class,
            \App\Enums\Type::class,
            \App\Enums\Wordlist\Type::class,
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

class UserDataHarness
{
    use UserData;
}

class KeyDataHarness
{
    use KeyData;
}

class PatternValidatorHarness
{
    use PatternValidator;

    public static function pattern(): array
    {
        return self::validatePattern();
    }

    public static function nameRule(?string $ignore = null): array
    {
        return self::validateName(ignore: $ignore);
    }

    public static function targetItemRule(): array
    {
        return self::validateTargetItem();
    }
}
