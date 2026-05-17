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
use App\Filament\Widgets\Concerns\InteractsWithSecurityWidgetData;
use App\Models\Action;
use App\Models\Decision;
use App\Models\Defender;
use App\Models\Label;
use App\Models\Principle;
use App\Models\Report;
use App\Models\Timeline;
use App\Models\User;
use App\Traits\Filament\Specifics\Action\ActionData;
use App\Traits\Filament\Specifics\Decision\DecisionData;
use App\Traits\Filament\Specifics\Defender\DefenderData;
use App\Traits\Filament\Specifics\Engine\EngineData;
use App\Traits\Filament\Specifics\GeneralData;
use App\Traits\Filament\Specifics\Rule\RuleData;
use Filament\Actions\Action as FilamentAction;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;

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

    protected function callFilamentAction(FilamentAction|BulkAction $action, mixed ...$arguments): void
    {
        $closure = $action->getActionFunction();

        $this->assertNotNull($closure, 'Filament action should expose a closure.');

        $closure(...$arguments);
    }

    protected function callAttachDetachLifecycleHooks(Defender $defender, Principle $principle, Decision $decision): void
    {
        $ruleTarget = \App\Models\Target::query()->create([
            'name' => 'button-target-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'type' => TargetType::Getter->value,
            'datatype' => Datatype::String->value,
        ]);
        $rule = \App\Models\Rule::query()->create([
            'name' => 'button-rule-'.Str::lower(Str::random(6)),
            'phase' => Phase::One->value,
            'target_id' => $ruleTarget->id,
            'comparator' => Comparator::Mirror->value,
            'is_inversed' => false,
            'configurations' => ['string' => 'needle'],
        ]);

        $defenderTable = $this->mockRelationTable($defender, new Principle());
        $this->callFilamentAfter(\App\Filament\Components\Principle\PrincipleTable::attachPrinciplesAndLockButton(), ['recordId' => null], $defenderTable);
        $this->callFilamentAfter(\App\Filament\Components\Principle\PrincipleTable::attachPrinciplesAndLockButton(), ['recordId' => [$principle->id]], $defenderTable);
        $this->callFilamentAfter(\App\Filament\Components\Principle\PrincipleTable::detachPrinciplesAndUnlockButton(), null, $defenderTable);
        $this->callFilamentAfter(\App\Filament\Components\Principle\PrincipleTable::detachPrinciplesAndUnlockButton(), $principle, $defenderTable);
        $this->callFilamentAfter(\App\Filament\Components\Principle\PrincipleTable::detachPrinciplesAndUnlockBulkButton(), collect(), $defenderTable);
        $this->callFilamentAfter(\App\Filament\Components\Principle\PrincipleTable::detachPrinciplesAndUnlockBulkButton(), collect([$principle]), $defenderTable);

        $decisionTable = $this->mockRelationTable($defender, new Decision());
        $this->callFilamentAfter(\App\Filament\Components\Decision\DecisionTable::attachDecisionsAndLockButton(), ['recordId' => null], $decisionTable);
        $this->callFilamentAfter(\App\Filament\Components\Decision\DecisionTable::attachDecisionsAndLockButton(), ['recordId' => [$decision->id]], $decisionTable);
        $this->callFilamentAfter(\App\Filament\Components\Decision\DecisionTable::detachDecisionsAndUnlockButton(), null, $decisionTable);
        $this->callFilamentAfter(\App\Filament\Components\Decision\DecisionTable::detachDecisionsAndUnlockButton(), $decision, $decisionTable);
        $this->callFilamentAfter(\App\Filament\Components\Decision\DecisionTable::detachDecisionsAndUnlockBulkButton(), collect(), $decisionTable);
        $this->callFilamentAfter(\App\Filament\Components\Decision\DecisionTable::detachDecisionsAndUnlockBulkButton(), collect([$decision]), $decisionTable);

        $principleTable = $this->mockRelationTable($principle, new \App\Models\Rule());
        $this->callFilamentAfter(\App\Filament\Components\Rule\RuleTable::attachRulesAndLockButton(), ['recordId' => null], $principleTable);
        $this->callFilamentAfter(\App\Filament\Components\Rule\RuleTable::attachRulesAndLockButton(), ['recordId' => [$rule->id]], $principleTable);
        $this->callFilamentAfter(\App\Filament\Components\Rule\RuleTable::detachRulesAndUnlockButton(), null, $principleTable);
        $this->callFilamentAfter(\App\Filament\Components\Rule\RuleTable::detachRulesAndUnlockButton(), $rule, $principleTable);
        $this->callFilamentAfter(\App\Filament\Components\Rule\RuleTable::detachRulesAndUnlockBulkButton(), collect(), $principleTable);
        $this->callFilamentAfter(\App\Filament\Components\Rule\RuleTable::detachRulesAndUnlockBulkButton(), collect([$rule]), $principleTable);
    }

    protected function callAdditionalSpecificButtonBranches(Defender $defender, Principle $principle, Decision $decision): void
    {
        $failureAction = new class
        {
            public int $failures = 0;

            public function failure(): void
            {
                $this->failures++;
            }
        };

        $this->callFilamentAction(\App\Filament\Components\Decision\DecisionForm::testRequestButton(), '', $failureAction);
        Http::fake(fn () => throw new \RuntimeException('request failed'));
        $this->callFilamentAction(\App\Filament\Components\Decision\DecisionForm::testRequestButton(), 'https://example.test/fails', $failureAction);
        $this->assertSame(2, $failureAction->failures);
        Http::fake(['*' => Http::response(['ok' => true])]);

        $this->callFilamentAction(\App\Filament\Components\Decision\DecisionTable::implementDecisionButton(), $decision);
        $this->callFilamentAction(\App\Filament\Components\Decision\DecisionTable::implementDecisionBulkButton($defender), collect());
        $this->callFilamentAction(\App\Filament\Components\Decision\DecisionTable::suspendDecisionButton($defender), $decision);
        $this->callFilamentAction(\App\Filament\Components\Decision\DecisionTable::suspendDecisionBulkButton($defender), collect([$decision]));

        $this->callFilamentAction(\App\Filament\Components\Principle\PrincipleTable::applyPrincipleButton(), $principle);
        $this->callFilamentAction(\App\Filament\Components\Principle\PrincipleTable::applyPrincipleBulkButton($defender), collect());
        $this->callFilamentAction(\App\Filament\Components\Principle\PrincipleTable::revokePrincipleButton($defender), $principle);
        $this->callFilamentAction(\App\Filament\Components\Principle\PrincipleTable::revokePrincipleBulkButton($defender), collect([$principle]));

        $pending = $this->filamentDefender(DeploymentStatus::Pending->value);
        $failed = $this->filamentDefender(DeploymentStatus::Failed->value);
        $this->callFilamentAction(\App\Filament\Components\Defender\DefenderTable::deployDefenderButton(), $pending);
        $this->callFilamentAction(\App\Filament\Components\Defender\DefenderTable::cancelDefenderButton(), $failed);
        $this->callFilamentAction(\App\Filament\Components\Defender\DefenderForm::followDefenderButton(), null, fn (string $key, mixed $value) => $this->assertSame('log', $key));
        Http::fake(fn () => throw new \RuntimeException('follow failed'));
        $this->callFilamentAction(\App\Filament\Components\Defender\DefenderForm::followDefenderButton(), $defender, fn (string $key, mixed $value) => $this->assertSame('log', $key));

        $defender->forceFill(['last_response_details' => ['ok' => true]])->save();
        $this->callFilamentAction(\App\Filament\Components\Defender\DefenderForm::refreshDefenderButton(), $defender, fn (string $key, mixed $value) => $this->assertSame('last_response_details', $key));
        $defender->forceFill(['last_response_details' => 42])->save();
        $this->callFilamentAction(\App\Filament\Components\Defender\DefenderForm::refreshDefenderButton(), $defender, fn (string $key, mixed $value) => $this->assertSame('last_response_details', $key));

        $timelineAction = \App\Filament\Components\Timeline\TimelineTable::openResourceButton();
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

    protected function callGeneralButtonClosures(): void
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
            \App\Filament\Components\Action\ActionTable::createButton(),
            'mutateDataUsing',
            ['name' => 'created', 'type' => ActionType::Allow->value],
        ));
        $this->assertIsArray($this->callClosureProperty(
            \App\Filament\Components\Action\ActionTable::viewButton(),
            'mutateRecordDataUsing',
            ['type' => ActionType::Allow->value, 'configurations' => []],
        ));
        $this->assertIsArray($this->callClosureProperty(
            \App\Filament\Components\Action\ActionTable::editButton(),
            'mutateRecordDataUsing',
            ['type' => ActionType::Allow->value, 'configurations' => []],
        ));
        $this->assertIsArray($this->callClosureProperty(
            \App\Filament\Components\Action\ActionTable::editButton(),
            'mutateDataUsing',
            ['name' => 'edited', 'type' => ActionType::Allow->value],
        ));

        $table = $this->mockRelationTable($label, new Action());
        $this->callFilamentAfter(\App\Filament\Components\Action\ActionTable::attachAndLockButton(), ['recordId' => null], $table);
        $this->callFilamentAfter(\App\Filament\Components\Action\ActionTable::attachAndLockButton(), ['recordId' => [$action->id]], $table);
        $this->callFilamentAfter(\App\Filament\Components\Action\ActionTable::detachAndUnlockButton(), null);
        $this->callFilamentAfter(\App\Filament\Components\Action\ActionTable::detachAndUnlockButton(), $action);

        $this->callFilamentAction(\App\Filament\Components\Action\ActionTable::deleteUnlockedBulkButton(), collect([$action]));

        $cloneSource = Action::query()->create([
            'name' => 'general-button-clone-'.Str::lower(Str::random(6)),
            'type' => ActionType::Allow->value,
            'is_locked' => true,
        ]);
        $cloneSource->labels()->attach($label->id);
        $this->callFilamentAction(\App\Filament\Components\Action\ActionTable::cloneButton(), $cloneSource);

        $detachAction = \App\Filament\Components\Action\ActionTable::detachAndUnlockBulkButton();
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
        $this->callFilamentAction(\App\Filament\Components\User\UserTable::deleteMultiUserButton(), collect([$current, $regular]));

        $permission = \App\Models\Permission::query()->create([
            'name' => 'button-permission-'.Str::lower(Str::random(6)),
            'applied_for' => 'User',
            'action' => 'view',
        ]);
        $permission->users()->attach($otherRoot->id);
        $livewire = Mockery::mock();
        $livewire->shouldReceive('getOwnerRecord')->andReturn($permission);
        $this->actingAs($regular);
        $this->callFilamentAction(\App\Filament\Components\User\UserTable::detachMultiUserButton(), collect([$regular, $otherRoot]), $livewire);
        $this->actingAs($current);
    }

    protected function callFilamentAfter(object $action, mixed ...$arguments): void
    {
        $reflection = new \ReflectionProperty($action, 'after');
        $closure = $reflection->getValue($action);

        $this->assertNotNull($closure, 'Filament action should expose an after hook.');

        $closure(...$arguments);
    }

    protected function callClosureProperty(object $object, string $property, mixed ...$arguments): mixed
    {
        $reflection = new \ReflectionProperty($object, $property);
        $closure = $reflection->getValue($object);

        $this->assertNotNull($closure, "{$property} should contain a closure.");

        return $closure(...$arguments);
    }

    protected function callComponentClosure(object $object, string $property, mixed ...$arguments): mixed
    {
        $closure = $this->componentClosure($object, $property);

        return $closure(...$arguments);
    }

    protected function componentClosure(object $object, string $property): \Closure
    {
        $reflection = new \ReflectionProperty($object, $property);
        $value = $reflection->getValue($object);
        $closure = $value instanceof \Closure ? $value : collect($value)->first(fn ($item) => $item instanceof \Closure);

        $this->assertNotNull($closure, "{$property} should contain a closure.");

        return $closure;
    }

    protected function formattedComponentState(object $component, mixed $state): mixed
    {
        $hydrated = $this->componentClosure($component, 'afterStateHydrated');
        $callback = (new \ReflectionFunction($hydrated))->getStaticVariables()['callback'] ?? null;

        $this->assertInstanceOf(\Closure::class, $callback);

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

    protected function assertWidgetPayloads(string $class, Defender $defender): void
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

    protected function repeaterValueRules(object $repeater): \Closure
    {
        $children = new \ReflectionProperty($repeater, 'childComponents');

        $valueField = collect($children->getValue($repeater)['default'] ?? [])
            ->first(fn ($component) => method_exists($component, 'getName') && $component->getName() === 'value');

        $this->assertNotNull($valueField);

        $reflection = new \ReflectionProperty($valueField, 'rules');

        foreach ($reflection->getValue($valueField) as [$rule]) {
            if ($rule instanceof \Closure) {
                return $rule;
            }
        }

        $this->fail('Expected value field to expose closure rules.');
    }

    protected function filamentWordlist(): \App\Models\Wordlist
    {
        return \App\Models\Wordlist::query()->create([
            'name' => 'wordlist-'.Str::lower(Str::random(6)),
            'type' => \App\Enums\Wordlist\Type::Json->value,
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

class FilamentActionDataHarness
{
    use ActionData;
}

class FilamentDecisionDataHarness
{
    use DecisionData;
}

class FilamentDefenderDataHarness
{
    use DefenderData;
}

class FilamentEngineDataHarness
{
    use EngineData;
}

class FilamentGeneralDataHarness
{
    use GeneralData;
}

class FilamentRuleDataHarness
{
    use RuleData;
}

class FieldHarness
{
    use \App\Traits\Filament\Generals\Components\Field;
}

class PrincipleFieldHarness
{
    use \App\Traits\Filament\Specifics\Principle\PrincipleField;
}

class EngineFieldHarness
{
    use \App\Traits\Filament\Specifics\Engine\EngineField;
}

class KeyColumnHarness
{
    use \App\Traits\Filament\Specifics\Key\KeyColumn;
}

class ButtonHarness
{
    use \App\Traits\Filament\Generals\Components\Button;

    public static function createForm(array $data): array
    {
        return [...$data, 'created' => true];
    }

    public static function editForm(array $data): array
    {
        return [...$data, 'edited' => true];
    }

    public static function saveForm(array $data): array
    {
        return [...$data, 'saved' => true];
    }
}

class EditPageHarness
{
    use \App\Traits\Filament\Generals\Pages\EditPage;

    public array $refreshed = [];

    public function refreshFormData(array $statePaths): void
    {
        $this->refreshed = $statePaths;
    }

    public function headerActionsPublic(): array
    {
        return $this->getHeaderActions();
    }

    public function beforeFillPublic(array $data): array
    {
        return $this->mutateFormDataBeforeFill($data);
    }

    public function beforeSavePublic(array $data): array
    {
        return $this->mutateFormDataBeforeSave($data);
    }
}

class CreatePageHarness
{
    use \App\Traits\Filament\Generals\Pages\CreatePage;

    public function beforeCreatePublic(array $data): array
    {
        return $this->mutateFormDataBeforeCreate($data);
    }
}

class RedirectListPageHarness
{
    use \App\Traits\Filament\Generals\Pages\Navigations\RedirectListPage;

    public function getResource(): string
    {
        return \App\Filament\Resources\Labels\LabelResource::class;
    }

    public function redirectUrlPublic(): string
    {
        return $this->getRedirectUrl();
    }
}

class WidgetDataHarness
{
    use InteractsWithSecurityWidgetData;

    public ?Model $record = null;

    public function currentDefenderPublic(): ?Defender
    {
        return $this->currentDefender();
    }

    public function reportsQueryPublic(?Defender $defender = null): Builder
    {
        return $this->reportsQuery($defender);
    }

    public function timelinesQueryPublic(?Defender $defender = null): Builder
    {
        return $this->timelinesQuery($defender);
    }

    public function countTodayPublic(Builder $query): int
    {
        return $this->countToday($query);
    }

    public function dateCountSeriesPublic(Builder $query, int $days): array
    {
        return $this->dateCountSeries($query, $days);
    }

    public function topReportJsonValuesPublic(string $column, string $path, ?Defender $defender = null): Collection
    {
        return $this->topReportJsonValues($column, $path, $defender);
    }

    public function uniqueReportJsonCountPublic(string $column, string $path, ?Defender $defender = null): int
    {
        return $this->uniqueReportJsonCount($column, $path, $defender);
    }

    public function topTriggeredActionsPublic(?Defender $defender = null): Collection
    {
        return $this->topTriggeredActions($defender);
    }

    public function topReportingDefendersPublic(): Collection
    {
        return $this->topReportingDefenders();
    }

    public function groupedDefenderCountsPublic(string $column): Collection
    {
        return $this->groupedDefenderCounts($column);
    }

    public function groupedPrincipleValidationCountsPublic(): Collection
    {
        return $this->groupedPrincipleValidationCounts();
    }

    public function groupedTimelineActionsPublic(?Defender $defender = null): Collection
    {
        return $this->groupedTimelineActions($defender);
    }

    public function policyCoveragePublic(?Defender $defender): array
    {
        return $this->policyCoverage($defender);
    }

    public function reportScatterPointsPublic(?Defender $defender = null): array
    {
        return $this->reportScatterPoints($defender);
    }

    public function bubblePointsPublic(Collection $series): array
    {
        return $this->bubblePoints($series);
    }

    public function labelsOrEmptyPublic(Collection $series): array
    {
        return $this->labelsOrEmpty($series);
    }

    public function valuesOrZeroPublic(Collection $series): array
    {
        return $this->valuesOrZero($series);
    }

    public function formatNumberPublic(int|float $value): string
    {
        return $this->formatNumber($value);
    }

    public function chartPalettePublic(): array
    {
        return $this->chartPalette();
    }

    protected function jsonValueExpression(string $column, string $path): string
    {
        return "json_extract({$column}, '{$path}')";
    }
}
