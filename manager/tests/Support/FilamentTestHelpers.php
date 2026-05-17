<?php

namespace Tests\Support;

use App\Enums\Action\Type as ActionType;
use App\Enums\Datatype;
use App\Enums\Decision\Action as DecisionAction;
use App\Enums\Decision\Condition;
use App\Enums\Decision\Direction;
use App\Enums\Defender\DeploymentStatus;
use App\Enums\Defender\Status;
use App\Enums\Engine\Hash;
use App\Enums\Engine\Type as EngineType;
use App\Enums\Phase;
use App\Enums\Principle\ValidationStatus;
use App\Enums\Rule\Comparator;
use App\Enums\Type as TargetType;
use App\Enums\Wordlist\Type as WordlistType;
use App\Filament\Components\Action\ActionTable;
use App\Filament\Components\Decision\DecisionForm;
use App\Filament\Components\Decision\DecisionTable;
use App\Filament\Components\Defender\DefenderForm;
use App\Filament\Components\Defender\DefenderTable;
use App\Filament\Components\Principle\PrincipleTable;
use App\Filament\Components\Rule\RuleTable;
use App\Filament\Components\Timeline\TimelineTable;
use App\Filament\Components\User\UserTable;
use App\Models\Action;
use App\Models\Decision;
use App\Models\Defender;
use App\Models\Label;
use App\Models\Permission;
use App\Models\Principle;
use App\Models\Report;
use App\Models\Rule;
use App\Models\Target;
use App\Models\Timeline;
use App\Models\User;
use App\Models\Wordlist;
use Closure;
use Filament\Actions\Action as FilamentAction;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery;
use PHPUnit\Framework\AssertionFailedError;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;
use RuntimeException;

trait FilamentTestHelpers
{
    protected function enginePayloads(): array
    {
        return [
            ['type' => EngineType::IndexOf->value, 'position' => 0],
            ['type' => EngineType::Merge->value, 'separator' => ','],
            ['type' => EngineType::Addition->value, 'digit' => 1],
            ['type' => EngineType::Subtraction->value, 'digit' => 1],
            ['type' => EngineType::Multiplication->value, 'digit' => 2],
            ['type' => EngineType::Division->value, 'digit' => 2],
            ['type' => EngineType::PowerOf->value, 'digit' => 2],
            ['type' => EngineType::Remainder->value, 'digit' => 2],
            ['type' => EngineType::ToString->value],
            ['type' => EngineType::Lower->value],
            ['type' => EngineType::Upper->value],
            ['type' => EngineType::Capitalize->value],
            ['type' => EngineType::Trim->value],
            ['type' => EngineType::TrimLeft->value],
            ['type' => EngineType::TrimRight->value],
            ['type' => EngineType::RemoveWhitespace->value],
            ['type' => EngineType::Length->value],
            ['type' => EngineType::Hash->value, 'hash_method' => Hash::Sha256->value],
            ['type' => EngineType::Split->value, 'separator' => ','],
        ];
    }

    protected function rulePayloads(): array
    {
        return [
            ['comparator' => Comparator::Equal->value, 'number_value' => 1],
            ['comparator' => Comparator::GreaterThan->value, 'number_value' => 1],
            ['comparator' => Comparator::LessThan->value, 'number_value' => 1],
            ['comparator' => Comparator::GreaterThanOrEqual->value, 'number_value' => 1],
            ['comparator' => Comparator::LessThanOrEqual->value, 'number_value' => 1],
            ['comparator' => Comparator::InRange->value, 'number_from_value' => 1, 'number_to_value' => 2],
            ['comparator' => Comparator::Contains->value, 'string_value' => 'needle'],
            ['comparator' => Comparator::Match->value, 'string_value' => 'needle'],
            ['comparator' => Comparator::Mirror->value, 'string_value' => 'needle'],
            ['comparator' => Comparator::StartsWith->value, 'string_value' => 'pre'],
            ['comparator' => Comparator::EndsWith->value, 'string_value' => 'post'],
            ['comparator' => Comparator::RegExp->value, 'string_value' => '/test/'],
            ['comparator' => Comparator::Similar->value],
            ['comparator' => Comparator::Search->value],
            ['comparator' => Comparator::Check->value],
            ['comparator' => Comparator::CheckRegExp->value],
        ];
    }

    protected function optionalMethodArguments(ReflectionMethod $method, Defender $defender): array
    {
        $arguments = [];

        foreach ($method->getParameters() as $parameter) {
            if ($parameter->getName() === 'defender') {
                $arguments[] = $defender;
            }
        }

        return $arguments;
    }

    protected function callFilamentAction(FilamentAction|BulkAction $action, mixed ...$arguments)
    {
        $closure = $action->getActionFunction();

        $this->assertNotNull($closure, 'Filament action should expose a closure.');

        $closure(...$arguments);
    }

    protected function callAttachDetachLifecycleHooks(Defender $defender, Principle $principle, Decision $decision)
    {
        $ruleTarget = Target::query()->create([
            'name' => 'button-target-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'type' => TargetType::Getter->value,
            'datatype' => Datatype::String->value,
        ]);
        $rule = Rule::query()->create([
            'name' => 'button-rule-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'target_id' => $ruleTarget->id,
            'comparator' => Comparator::Mirror->value,
            'is_inversed' => false,
            'configurations' => ['string' => 'needle'],
        ]);

        $defenderTable = $this->mockRelationTable($defender, new Principle());
        $this->callFilamentAfter(PrincipleTable::attachPrinciplesAndLockButton(), ['recordId' => null], $defenderTable);
        $this->callFilamentAfter(PrincipleTable::attachPrinciplesAndLockButton(), ['recordId' => [$principle->id]], $defenderTable);
        $this->callFilamentAfter(PrincipleTable::detachPrinciplesAndUnlockButton(), null, $defenderTable);
        $this->callFilamentAfter(PrincipleTable::detachPrinciplesAndUnlockButton(), $principle, $defenderTable);
        $this->callFilamentAfter(PrincipleTable::detachPrinciplesAndUnlockBulkButton(), collect(), $defenderTable);
        $this->callFilamentAfter(PrincipleTable::detachPrinciplesAndUnlockBulkButton(), collect([$principle]), $defenderTable);

        $decisionTable = $this->mockRelationTable($defender, new Decision());
        $this->callFilamentAfter(DecisionTable::attachDecisionsAndLockButton(), ['recordId' => null], $decisionTable);
        $this->callFilamentAfter(DecisionTable::attachDecisionsAndLockButton(), ['recordId' => [$decision->id]], $decisionTable);
        $this->callFilamentAfter(DecisionTable::detachDecisionsAndUnlockButton(), null, $decisionTable);
        $this->callFilamentAfter(DecisionTable::detachDecisionsAndUnlockButton(), $decision, $decisionTable);
        $this->callFilamentAfter(DecisionTable::detachDecisionsAndUnlockBulkButton(), collect(), $decisionTable);
        $this->callFilamentAfter(DecisionTable::detachDecisionsAndUnlockBulkButton(), collect([$decision]), $decisionTable);

        $principleTable = $this->mockRelationTable($principle, new Rule());
        $this->callFilamentAfter(RuleTable::attachRulesAndLockButton(), ['recordId' => null], $principleTable);
        $this->callFilamentAfter(RuleTable::attachRulesAndLockButton(), ['recordId' => [$rule->id]], $principleTable);
        $this->callFilamentAfter(RuleTable::detachRulesAndUnlockButton(), null, $principleTable);
        $this->callFilamentAfter(RuleTable::detachRulesAndUnlockButton(), $rule, $principleTable);
        $this->callFilamentAfter(RuleTable::detachRulesAndUnlockBulkButton(), collect(), $principleTable);
        $this->callFilamentAfter(RuleTable::detachRulesAndUnlockBulkButton(), collect([$rule]), $principleTable);
    }

    protected function callAdditionalSpecificButtonBranches(Defender $defender, Principle $principle, Decision $decision)
    {
        $failureAction = new class
        {
            public int $failures = 0;

            public function failure()
            {
                $this->failures++;
            }
        };

        $this->callFilamentAction(DecisionForm::testRequestButton(), '', $failureAction);
        Http::fake(fn () => throw new RuntimeException('request failed'));
        $this->callFilamentAction(DecisionForm::testRequestButton(), 'https://example.test/fails', $failureAction);
        $this->assertSame(2, $failureAction->failures);
        Http::fake(['*' => Http::response(['ok' => true])]);

        $this->callFilamentAction(DecisionTable::implementDecisionButton(), $decision);
        $this->callFilamentAction(DecisionTable::implementDecisionBulkButton($defender), collect());
        $this->callFilamentAction(DecisionTable::suspendDecisionButton($defender), $decision);
        $this->callFilamentAction(DecisionTable::suspendDecisionBulkButton($defender), collect([$decision]));

        $this->callFilamentAction(PrincipleTable::applyPrincipleButton(), $principle);
        $this->callFilamentAction(PrincipleTable::applyPrincipleBulkButton($defender), collect());
        $this->callFilamentAction(PrincipleTable::revokePrincipleButton($defender), $principle);
        $this->callFilamentAction(PrincipleTable::revokePrincipleBulkButton($defender), collect([$principle]));

        $pending = $this->filamentDefender(DeploymentStatus::Pending->value);
        $failed = $this->filamentDefender(DeploymentStatus::Failed->value);
        $this->callFilamentAction(DefenderTable::deployDefenderButton(), $pending);
        $this->callFilamentAction(DefenderTable::cancelDefenderButton(), $failed);
        $this->callFilamentAction(DefenderForm::followDefenderButton(), null, fn (string $key, mixed $value) => $this->assertSame('log', $key));
        Http::fake(fn () => throw new RuntimeException('follow failed'));
        $this->callFilamentAction(DefenderForm::followDefenderButton(), $defender, fn (string $key, mixed $value) => $this->assertSame('log', $key));

        $defender->forceFill(['last_response_details' => ['ok' => true]])->save();
        $this->callFilamentAction(DefenderForm::refreshDefenderButton(), $defender, fn (string $key, mixed $value) => $this->assertSame('last_response_details', $key));
        $defender->forceFill(['last_response_details' => 42])->save();
        $this->callFilamentAction(DefenderForm::refreshDefenderButton(), $defender, fn (string $key, mixed $value) => $this->assertSame('last_response_details', $key));

        $timelineAction = TimelineTable::openResourceButton();
        $this->assertFalse($this->callClosureProperty($timelineAction, 'isVisible', fn (string $key) => null));
        $this->assertTrue($this->callClosureProperty($timelineAction, 'isVisible', fn (string $key) => match ($key) {
            'resource_type' => Action::class,
            'resource_id' => $decision->id,
            default => null,
        }));
        $this->assertNull($this->callClosureProperty($timelineAction, 'url', fn (string $key) => null));
        $this->assertIsString($this->callClosureProperty($timelineAction, 'url', fn (string $key) => match ($key) {
            'resource_type' => Action::class,
            'resource_id' => $decision->id,
            default => null,
        }));
    }

    protected function callGeneralButtonClosures()
    {
        $action = Action::query()->create([
            'name' => 'general-button-action-'.Str::lower(Str::random(6)),
            'type' => ActionType::Allow->value,
            'is_locked' => false,
        ]);
        $label = Label::query()->create([
            'name' => 'general-button-label-'.Str::lower(Str::random(6)),
            'color' => '#123456',
        ]);
        $action->labels()->attach($label->id);

        $this->assertIsArray($this->callClosureProperty(
            ActionTable::createButton(),
            'mutateDataUsing',
            ['name' => 'created', 'type' => ActionType::Allow->value],
        ));
        $this->assertIsArray($this->callClosureProperty(
            ActionTable::viewButton(),
            'mutateRecordDataUsing',
            ['type' => ActionType::Allow->value, 'configurations' => []],
        ));
        $this->assertIsArray($this->callClosureProperty(
            ActionTable::editButton(),
            'mutateRecordDataUsing',
            ['type' => ActionType::Allow->value, 'configurations' => []],
        ));
        $this->assertIsArray($this->callClosureProperty(
            ActionTable::editButton(),
            'mutateDataUsing',
            ['name' => 'edited', 'type' => ActionType::Allow->value],
        ));

        $table = $this->mockRelationTable($label, new Action());
        $this->callFilamentAfter(ActionTable::attachAndLockButton(), ['recordId' => null], $table);
        $this->callFilamentAfter(ActionTable::attachAndLockButton(), ['recordId' => [$action->id]], $table);
        $this->callFilamentAfter(ActionTable::detachAndUnlockButton(), null);
        $this->callFilamentAfter(ActionTable::detachAndUnlockButton(), $action);

        $this->callFilamentAction(ActionTable::deleteUnlockedBulkButton(), collect([$action]));

        $cloneSource = Action::query()->create([
            'name' => 'general-button-clone-'.Str::lower(Str::random(6)),
            'type' => ActionType::Allow->value,
            'is_locked' => true,
        ]);
        $cloneSource->labels()->attach($label->id);
        $this->callFilamentAction(ActionTable::cloneButton(), $cloneSource);

        $detachAction = ActionTable::detachAndUnlockBulkButton();
        $relationship = Mockery::mock();
        $relationship->shouldReceive('getPivotAccessor')->andReturn('pivot');
        $relationship->shouldReceive('detach')->with($cloneSource)->once();
        $relationship->shouldReceive('getRelated')->andReturn(new Action());
        $bulkTable = Mockery::mock();
        $bulkTable->shouldReceive('getRelationship')->andReturn($relationship);
        $bulkTable->shouldReceive('allowsDuplicates')->andReturn(false);
        $this->callFilamentAction($detachAction, collect([$cloneSource]), $bulkTable);

        $current = User::factory()->create(['is_root' => true, 'is_verified' => true, 'is_activated' => true]);
        $regular = User::factory()->create(['is_root' => false, 'is_verified' => true, 'is_activated' => true]);
        $otherRoot = User::factory()->create(['is_root' => true, 'is_verified' => true, 'is_activated' => true]);
        $this->actingAs($current);
        $this->callFilamentAction(UserTable::deleteMultiUserButton(), collect([$current, $regular]));

        $permission = Permission::query()->create([
            'name' => 'button-permission-'.Str::lower(Str::random(6)),
            'applied_for' => 'User',
            'action' => 'view',
        ]);
        $permission->users()->attach($otherRoot->id);
        $livewire = Mockery::mock();
        $livewire->shouldReceive('getOwnerRecord')->andReturn($permission);
        $this->actingAs($regular);
        $this->callFilamentAction(UserTable::detachMultiUserButton(), collect([$regular, $otherRoot]), $livewire);
        $this->actingAs($current);
    }

    protected function callFilamentAfter(object $action, mixed ...$arguments)
    {
        $reflection = new ReflectionProperty($action, 'after');
        $closure = $reflection->getValue($action);

        $this->assertNotNull($closure, 'Filament action should expose an after hook.');

        $closure(...$arguments);
    }

    protected function callClosureProperty(object $object, string $property, mixed ...$arguments): mixed
    {
        $reflection = new ReflectionProperty($object, $property);
        $closure = $reflection->getValue($object);

        $this->assertNotNull($closure, "{$property} should contain a closure.");

        return $closure(...$arguments);
    }

    protected function callComponentClosure(object $object, string $property, mixed ...$arguments): mixed
    {
        $closure = $this->componentClosure($object, $property);

        return $closure(...$arguments);
    }

    protected function componentClosure(object $object, string $property): Closure
    {
        $reflection = new ReflectionProperty($object, $property);
        $value = $reflection->getValue($object);
        $closure = $value instanceof Closure ? $value : collect($value)->first(fn ($item) => $item instanceof Closure);

        $this->assertNotNull($closure, "{$property} should contain a closure.");

        return $closure;
    }

    protected function formattedComponentState(object $component, mixed $state): mixed
    {
        $hydrated = $this->componentClosure($component, 'afterStateHydrated');
        $callback = (new ReflectionFunction($hydrated))->getStaticVariables()['callback'] ?? null;

        $this->assertInstanceOf(Closure::class, $callback);

        return $callback($state);
    }

    protected function invokePageMethod(string $class, string $method, array $data): mixed
    {
        $page = new $class();
        $reflection = new ReflectionMethod($page, $method);

        return $reflection->invoke($page, $data);
    }

    protected function mockRelationTable(Model $owner, Model $related): object
    {
        $livewire = Mockery::mock();
        $livewire->shouldReceive('dispatch')->andReturnSelf();
        $livewire->shouldReceive('to')->andReturnSelf();
        $livewire->shouldReceive('getPageClass')->andReturn('TestingPage');

        $relationship = Mockery::mock();
        $relationship->shouldReceive('getRelated')->andReturn($related);
        $relationship->shouldReceive('getParent')->andReturn($owner);

        $table = Mockery::mock();
        $table->shouldReceive('getRelationship')->andReturn($relationship);
        $table->shouldReceive('getLivewire')->andReturn($livewire);

        return $table;
    }

    protected function assertWidgetPayloads(string $class, Defender $defender)
    {
        $widget = new $class();

        if (property_exists($widget, 'record')) {
            $widget->record = $defender;
        }

        foreach (['getStats', 'getData', 'getOptions', 'getType'] as $method) {
            if (! method_exists($widget, $method)) {
                continue;
            }

            $reflection = new ReflectionMethod($widget, $method);
            if ($reflection->getNumberOfRequiredParameters() > 0) {
                continue;
            }

            $result = $reflection->invoke($widget);

            if ($method !== 'getOptions') {
                $this->assertNotNull($result, "{$class}::{$method} should return data.");
            }
        }

        if (method_exists($class, 'getHeading')) {
            $reflection = new ReflectionMethod($class, 'getHeading');

            if ($reflection->isPublic()) {
                $this->assertNotNull($reflection->invoke($widget));
            }
        }
    }

    protected function repeaterValueRules(object $repeater): Closure
    {
        $children = new ReflectionProperty($repeater, 'childComponents');

        $valueField = collect($children->getValue($repeater)['default'] ?? [])
            ->first(fn ($component) => method_exists($component, 'getName') && $component->getName() === 'value');

        $this->assertNotNull($valueField);

        $reflection = new ReflectionProperty($valueField, 'rules');

        foreach ($reflection->getValue($valueField) as [$rule]) {
            if ($rule instanceof Closure) {
                return $rule;
            }
        }

        throw new AssertionFailedError('Expected value field to expose closure rules.');
    }

    protected function filamentWordlist(): Wordlist
    {
        return Wordlist::query()->create([
            'name' => 'wordlist-'.Str::lower(Str::random(6)),
            'type' => WordlistType::Json->value,
            'word_json' => [['word' => 'alpha']],
        ]);
    }

    protected function filamentPrinciple(?string $validationStatus = ValidationStatus::Passed->value): Principle
    {
        return Principle::query()->create([
            'name' => 'principle-'.Str::lower(Str::random(6)),
            'level' => 1,
            'phase' => Phase::One->value,
            'validation_status' => $validationStatus,
        ]);
    }

    protected function filamentDefender(?string $deploymentStatus = DeploymentStatus::Successful->value): Defender
    {
        return Defender::query()->create([
            'name' => 'defender-'.Str::lower(Str::random(6)),
            'proxy_port' => random_int(9000, 9999),
            'status' => Status::Normal->value,
            'deployment_status' => $deploymentStatus,
            'environment_variables' => ['PROXY_BACKEND_URL' => 'http://localhost'],
        ]);
    }

    protected function classesUnder(string $path, callable $filter): array
    {
        $classes = [];
        $root = app_path();

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php' || ! $filter($file->getPathname())) {
                continue;
            }

            $relative = substr($file->getPathname(), strlen($root) + 1, -4);
            $classes[] = 'App\\'.str_replace(['/', '\\'], '\\', $relative);
        }

        sort($classes);

        return $classes;
    }
}
