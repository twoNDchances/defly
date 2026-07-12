<?php

namespace Tests\Feature\Gui\FilamentComponents;

use App\Enums\Decision\Action as DecisionAction;
use App\Enums\Decision\Condition;
use App\Enums\Decision\Direction;
use App\Enums\Defender\DeploymentStatus;
use App\Enums\Principle\ValidationStatus;
use App\Filament\Components\Decision\DecisionForm;
use App\Filament\Components\Decision\DecisionTable;
use App\Filament\Components\Defender\DefenderForm;
use App\Filament\Components\Defender\DefenderTable;
use App\Filament\Components\Key\KeyForm;
use App\Filament\Components\Principle\PrincipleTable;
use App\Filament\Components\Report\ReportTable;
use App\Filament\Components\User\UserForm;
use App\Filament\Components\Wordlist\WordlistTable;
use App\Models\Decision;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Tests\Support\FilamentTestHelpers;
use Tests\TestCase;

class FilamentButtonActionTest extends TestCase
{
    use FilamentTestHelpers;
    use RefreshDatabase;

    public function test_filament_button_factories_and_action_closures_are_resolvable(): void
    {
        Queue::fake();
        Http::fake(['*' => Http::response(['ok' => true])]);
        Storage::fake('local');

        /** @var User $user */
        $user = User::factory()->create([
            'is_root' => true,
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $this->actingAs($user);

        $defender = $this->filamentDefender();
        $pendingDefender = $this->filamentDefender(DeploymentStatus::Pending->value);
        $failedDefender = $this->filamentDefender(DeploymentStatus::Failed->value);
        $principle = $this->filamentPrinciple();
        $decision = Decision::query()->create([
            'name' => 'button-decision-'.Str::lower(Str::random(6)),
            'direction' => Direction::Request->value,
            'condition' => Condition::GreaterThanOrEqual->value,
            'score' => 5,
            'action' => DecisionAction::Allow->value,
        ]);
        $defender->principles()->attach($principle->id, ['order' => 1, 'is_applied' => true]);
        $defender->decisions()->attach($decision->id, ['order' => 1, 'is_implemented' => true]);

        foreach ($this->classesUnder(app_path('Filament/Components'), fn (string $file) => str_ends_with($file, 'Form.php') || str_ends_with($file, 'Table.php')) as $class) {
            foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if (! $method->isStatic() || $method->getNumberOfRequiredParameters() > 0) {
                    continue;
                }

                if (! str_contains($method->getName(), 'Button') && ! in_array($method->getName(), ['build'], true)) {
                    continue;
                }

                $result = $class::{$method->getName()}(...$this->optionalMethodArguments($method, $defender));
                $this->assertNotNull($result, "{$class}::{$method->getName()} should be resolvable.");
            }
        }

        $this->callFilamentAction(DefenderTable::deployDefenderButton(), $failedDefender);
        $this->assertSame(DeploymentStatus::Pending, $failedDefender->refresh()->deployment_status);

        $this->callFilamentAction(DefenderTable::deployDefenderBulkButton(), collect([$this->filamentDefender(DeploymentStatus::Failed->value), $pendingDefender]));
        $this->callFilamentAction(DefenderTable::cancelDefenderButton(), $defender);
        $this->callFilamentAction(DefenderTable::cancelDefenderBulkButton(), collect([$this->filamentDefender(), $pendingDefender]));
        $this->callFilamentAction(DefenderTable::deleteDoneBulkButton(), collect([$this->filamentDefender(DeploymentStatus::Failed->value), $pendingDefender]));
        $this->callFilamentAction(DefenderForm::followDefenderButton(), $defender, fn (string $key) => $this->assertSame('log', $key));
        $this->callFilamentAction(DefenderForm::refreshDefenderButton(), $defender, fn (string $key) => $this->assertSame('last_response_details', $key));

        $communicationDefender = $this->filamentDefender();
        $communicationPrinciple = $this->filamentPrinciple();
        $communicationDecision = Decision::query()->create([
            'name' => 'communication-decision-'.Str::lower(Str::random(6)),
            'direction' => Direction::Request->value,
            'condition' => Condition::GreaterThanOrEqual->value,
            'score' => 5,
            'action' => DecisionAction::Allow->value,
        ]);
        $communicationDefender->principles()->attach($communicationPrinciple->id, ['order' => 1, 'is_applied' => true]);
        $communicationDefender->decisions()->attach($communicationDecision->id, ['order' => 1, 'is_implemented' => true]);

        $this->callFilamentAction(PrincipleTable::validatePrincipleButton(), $principle);
        $this->assertSame(ValidationStatus::Pending, $principle->refresh()->validation_status);
        $this->callFilamentAction(PrincipleTable::validatePrincipleBulkButton(), collect([$this->filamentPrinciple(), $this->filamentPrinciple(ValidationStatus::Validating->value)]));
        $this->callFilamentAction(PrincipleTable::clonePrincipleButton(), $principle->refresh());
        $this->callFilamentAction(PrincipleTable::deleteUnlockedBulkButton(), collect([$this->filamentPrinciple(), $this->filamentPrinciple(ValidationStatus::Validating->value)]));
        $this->callFilamentAction(PrincipleTable::applyPrincipleButton($communicationDefender), $communicationPrinciple->refresh());
        $this->callFilamentAction(PrincipleTable::applyPrincipleBulkButton($communicationDefender), collect([$communicationPrinciple->refresh()]));
        $pivotPrinciple = $communicationDefender->principles()->first();
        $this->callFilamentAction(PrincipleTable::revokePrincipleButton($communicationDefender), $pivotPrinciple);
        $this->callFilamentAction(PrincipleTable::revokePrincipleBulkButton($communicationDefender), collect([$pivotPrinciple]));

        $this->callFilamentAction(DecisionForm::testRequestButton(), 'https://example.test', new class
        {
            public bool $failed = false;

            public function failure(): void
            {
                $this->failed = true;
            }
        });
        $this->callFilamentAction(DecisionTable::implementDecisionButton($communicationDefender), $communicationDecision->refresh());
        $this->callFilamentAction(DecisionTable::implementDecisionBulkButton($communicationDefender), collect([$communicationDecision->refresh()]));
        $pivotDecision = $communicationDefender->decisions()->first();
        $this->callFilamentAction(DecisionTable::suspendDecisionButton($communicationDefender), $pivotDecision);
        $this->callFilamentAction(DecisionTable::suspendDecisionBulkButton($communicationDefender), collect([$pivotDecision]));

        $report = Report::withoutEvents(fn () => Report::query()->create(['is_reviewed' => false]));
        $this->callFilamentAction(ReportTable::reviewReportButton(), $report);
        $this->assertTrue($report->refresh()->is_reviewed);
        $this->callFilamentAction(ReportTable::reviewReportBulkButton(), collect([Report::withoutEvents(fn () => Report::query()->create(['is_reviewed' => false]))]));

        $wordlist = $this->filamentWordlist();
        $this->callFilamentAction(WordlistTable::cloneWordlistButton(), $wordlist);

        $this->callFilamentAction(UserForm::generatePasswordButton(), fn (string $key) => $this->assertSame('password', $key));
        $this->callFilamentAction(KeyForm::generateTokenButton(), fn (string $key) => $this->assertSame('token', $key));

        $this->callAdditionalSpecificButtonBranches($communicationDefender, $communicationPrinciple, $communicationDecision);
        $this->callAttachDetachLifecycleHooks($communicationDefender, $communicationPrinciple, $communicationDecision);
        $this->callGeneralButtonClosures();
    }
}
