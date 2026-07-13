<?php

namespace Tests\Feature\Gui\FilamentComponents;

use App\Enums\Action\Type as ActionType;
use App\Enums\Datatype;
use App\Enums\Decision\Action as DecisionAction;
use App\Enums\Decision\Condition;
use App\Enums\Decision\Direction;
use App\Enums\Engine\Type as EngineType;
use App\Enums\Rule\Comparator;
use App\Enums\Wordlist\Type;
use App\Filament\Clusters\Authentication\Resources\Keys\Pages\EditKey;
use App\Filament\Clusters\Authentication\Resources\Users\Pages\EditUser;
use App\Filament\Clusters\Context\Resources\Engines\Pages\CreateEngine;
use App\Filament\Clusters\Infrastructure\Resources\Defenders\Pages\CreateDefender;
use App\Filament\Clusters\Initialization\Resources\Actions\Pages\CreateAction;
use App\Filament\Clusters\Initialization\Resources\Actions\Pages\EditAction;
use App\Filament\Clusters\Initialization\Resources\Decisions\Pages\CreateDecision;
use App\Filament\Clusters\Initialization\Resources\Rules\Pages\CreateRule;
use App\Filament\Components\Guard\GuardTable;
use App\Filament\Components\Wordlist\WordlistTable;
use App\Models\Action;
use App\Models\Wordlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery;
use Tests\Support\ButtonHarness;
use Tests\Support\CreatePageHarness;
use Tests\Support\EditPageHarness;
use Tests\Support\EngineFieldHarness;
use Tests\Support\FieldHarness;
use Tests\Support\FilamentTestHelpers;
use Tests\Support\KeyColumnHarness;
use Tests\Support\PrincipleFieldHarness;
use Tests\Support\RedirectListPageHarness;
use Tests\TestCase;

class GeneralFieldButtonColumnPageHelpersTest extends TestCase
{
    use FilamentTestHelpers;
    use RefreshDatabase;

    public function test_filament_remaining_field_button_column_and_page_helpers(): void
    {
        Storage::fake('local');

        $jsonPreview = FieldHarness::jsonPreview('payload');
        $this->assertNull($this->formattedComponentState($jsonPreview, null));
        $this->assertSame('already-json', $this->formattedComponentState($jsonPreview, 'already-json'));
        $this->assertStringContainsString('"ok"', $this->formattedComponentState($jsonPreview, ['ok' => true]));
        $this->assertSame('42', $this->formattedComponentState($jsonPreview, 42));

        $validationDetails = PrincipleFieldHarness::setValidationDetails();
        $this->assertSame('state', $this->formattedComponentState($validationDetails, 'state'));
        $this->assertStringContainsString('"failed"', $this->formattedComponentState($validationDetails, ['status' => 'failed']));
        $this->assertSame('99', $this->formattedComponentState($validationDetails, 99));

        $setValues = [];
        $setter = function (string $key, mixed $value) use (&$setValues): void {
            $setValues[$key] = $value;
        };
        $typeField = EngineFieldHarness::setType();
        $this->callComponentClosure($typeField, 'afterStateUpdated', EngineType::Split->value, $setter);
        $this->callComponentClosure($typeField, 'afterStateUpdated', EngineType::Length->value, $setter);
        $this->callComponentClosure($typeField, 'afterStateUpdated', EngineType::ToString->value, $setter);
        $this->assertSame(Datatype::String->value, $setValues['output_datatype']);

        $expiredAtColumn = KeyColumnHarness::getExpiredAt();
        $this->assertSame('danger', $this->callClosureProperty($expiredAtColumn, 'color', (object) ['expired_at' => now()->subMinute()]));
        $this->assertSame('info', $this->callClosureProperty($expiredAtColumn, 'color', (object) ['expired_at' => now()->addHours(2)]));
        $this->assertSame('info', $this->callClosureProperty($expiredAtColumn, 'color', (object) ['expired_at' => now()->addDays(10)]));

        $guardExpiredAtColumn = GuardTable::getExpiredAt();
        $this->assertTrue($guardExpiredAtColumn->isBadge());
        $this->assertSame('danger', $this->callClosureProperty($guardExpiredAtColumn, 'color', (object) ['expired_at' => now()->subMinute()]));
        $this->assertSame('warning', $this->callClosureProperty($guardExpiredAtColumn, 'color', (object) ['expired_at' => now()->addHours(2)]));
        $this->assertSame('success', $this->callClosureProperty($guardExpiredAtColumn, 'color', (object) ['expired_at' => now()->addDays(10)]));
        $this->assertSame('success', $this->callClosureProperty($guardExpiredAtColumn, 'color', (object) ['expired_at' => null]));

        $fileWordlist = Wordlist::query()->create([
            'name' => 'file-clone-'.Str::lower(Str::random(6)),
            'type' => Type::File->value,
            'word_file' => 'wordlists/source.txt',
        ]);
        Storage::put('wordlists/source.txt', "alpha\n");
        $this->callFilamentAction(WordlistTable::cloneWordlistButton(), $fileWordlist);

        $missingFileWordlist = Wordlist::query()->create([
            'name' => 'file-missing-'.Str::lower(Str::random(6)),
            'type' => Type::File->value,
            'word_file' => 'wordlists/missing.txt',
        ]);
        $this->callFilamentAction(WordlistTable::cloneWordlistButton(), $missingFileWordlist);

        $this->assertSame(['created' => true, 'saved' => true], $this->callClosureProperty(
            ButtonHarness::createButton(),
            'mutateDataUsing',
            [],
        ));
        $this->assertSame(['edited' => true, 'saved' => true], $this->callClosureProperty(
            ButtonHarness::editButton(),
            'mutateDataUsing',
            [],
        ));

        $record = Action::query()->create([
            'name' => 'duplicate-detach-'.Str::lower(Str::random(6)),
            'type' => ActionType::Allow->value,
        ]);
        $pivot = Mockery::mock();
        $pivot->shouldReceive('delete')->once();
        $record->setRelation('pivot', $pivot);
        $relationship = Mockery::mock();
        $relationship->shouldReceive('getPivotAccessor')->andReturn('pivot');
        $relationship->shouldReceive('getRelated')->andReturn(new Action);
        $duplicateTable = Mockery::mock();
        $duplicateTable->shouldReceive('getRelationship')->andReturn($relationship);
        $duplicateTable->shouldReceive('allowsDuplicates')->andReturn(true);
        $this->callFilamentAction(ButtonHarness::detachAndUnlockBulkButton(), collect([$record]), $duplicateTable);

        $emptyRelationship = Mockery::mock();
        $emptyRelationship->shouldReceive('getPivotAccessor')->andReturn('pivot');
        $emptyTable = Mockery::mock();
        $emptyTable->shouldReceive('getRelationship')->andReturn($emptyRelationship);
        $this->callFilamentAction(ButtonHarness::detachAndUnlockBulkButton(), collect(), $emptyTable);

        $editPage = new EditPageHarness;
        $editPage->refreshFormDataFromRelationManager();
        $editPage->refreshFormDataFromRelationManager(['deployment_status']);
        $this->assertSame(['deployment_status'], $editPage->refreshed);
        $this->assertNotEmpty($editPage->headerActionsPublic());
        $this->assertSame(['x' => 1], $editPage->beforeFillPublic(['x' => 1]));
        $this->assertSame(['x' => 2], $editPage->beforeSavePublic(['x' => 2]));

        $createPage = new CreatePageHarness;
        $this->assertSame(['created' => true], $createPage->beforeCreatePublic(['created' => true]));
        $this->assertIsString((new RedirectListPageHarness)->redirectUrlPublic());

        $this->assertIsArray($this->invokePageMethod(
            CreateAction::class,
            'mutateFormDataBeforeCreate',
            ['type' => ActionType::Allow->value],
        ));
        $this->assertIsArray($this->invokePageMethod(
            EditAction::class,
            'mutateFormDataBeforeSave',
            ['type' => ActionType::Allow->value],
        ));
        $this->assertIsArray($this->invokePageMethod(
            CreateDecision::class,
            'mutateFormDataBeforeCreate',
            ['direction' => Direction::Request->value, 'condition' => Condition::GreaterThanOrEqual->value, 'score' => 5, 'action' => DecisionAction::Allow->value],
        ));
        $this->assertIsArray($this->invokePageMethod(
            CreateRule::class,
            'mutateFormDataBeforeCreate',
            ['comparator' => Comparator::Mirror->value, 'string_value' => 'needle'],
        ));
        $this->assertIsArray($this->invokePageMethod(
            CreateEngine::class,
            'mutateFormDataBeforeCreate',
            ['type' => EngineType::Lower->value],
        ));
        $this->assertIsArray($this->invokePageMethod(
            CreateDefender::class,
            'mutateFormDataBeforeCreate',
            [
                'common_environment_variables' => [],
                'server_environment_variables' => [],
                'proxy_environment_variables' => [],
            ],
        ));
        $this->assertIsArray($this->invokePageMethod(
            EditKey::class,
            'mutateFormDataBeforeSave',
            ['token' => ''],
        ));
        $this->assertIsArray($this->invokePageMethod(
            EditUser::class,
            'mutateFormDataBeforeSave',
            ['password' => ''],
        ));
    }
}
