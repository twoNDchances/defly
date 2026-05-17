<?php

namespace Tests\Feature\Gui\FilamentComponents;

use App\Enums\Action\Type as ActionType;
use App\Enums\Decision\Action as DecisionAction;
use App\Enums\Decision\Condition;
use App\Enums\Decision\Direction;
use App\Enums\Defender\DeploymentStatus;
use App\Enums\Principle\ValidationStatus;
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

        $this->actingAs(User::factory()->create([
            'is_root' => true,
            'is_verified' => true,
            'is_activated' => true,
        ]));

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

        $this->callFilamentAction(\App\Filament\Components\Defender\DefenderTable::deployDefenderButton(), $failedDefender);
        $this->assertSame(DeploymentStatus::Pending, $failedDefender->fresh()->deployment_status);

        $this->callFilamentAction(\App\Filament\Components\Defender\DefenderTable::deployDefenderBulkButton(), collect([$this->filamentDefender(DeploymentStatus::Failed->value), $pendingDefender]));
        $this->callFilamentAction(\App\Filament\Components\Defender\DefenderTable::cancelDefenderButton(), $defender);
        $this->callFilamentAction(\App\Filament\Components\Defender\DefenderTable::cancelDefenderBulkButton(), collect([$this->filamentDefender(), $pendingDefender]));
        $this->callFilamentAction(\App\Filament\Components\Defender\DefenderTable::deleteDoneBulkButton(), collect([$this->filamentDefender(DeploymentStatus::Failed->value), $pendingDefender]));
        $this->callFilamentAction(\App\Filament\Components\Defender\DefenderForm::followDefenderButton(), $defender, fn (string $key, mixed $value) => $this->assertSame('log', $key));
        $this->callFilamentAction(\App\Filament\Components\Defender\DefenderForm::refreshDefenderButton(), $defender, fn (string $key, mixed $value) => $this->assertSame('last_response_details', $key));

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

        $this->callFilamentAction(\App\Filament\Components\Principle\PrincipleTable::validatePrincipleButton(), $principle);
        $this->assertSame(ValidationStatus::Pending, $principle->fresh()->validation_status);
        $this->callFilamentAction(\App\Filament\Components\Principle\PrincipleTable::validatePrincipleBulkButton(), collect([$this->filamentPrinciple(), $this->filamentPrinciple(ValidationStatus::Validating->value)]));
        $this->callFilamentAction(\App\Filament\Components\Principle\PrincipleTable::clonePrincipleButton(), $principle->fresh());
        $this->callFilamentAction(\App\Filament\Components\Principle\PrincipleTable::deleteUnlockedBulkButton(), collect([$this->filamentPrinciple(), $this->filamentPrinciple(ValidationStatus::Validating->value)]));
        $this->callFilamentAction(\App\Filament\Components\Principle\PrincipleTable::applyPrincipleButton($communicationDefender), $communicationPrinciple->fresh());
        $this->callFilamentAction(\App\Filament\Components\Principle\PrincipleTable::applyPrincipleBulkButton($communicationDefender), collect([$communicationPrinciple->fresh()]));
        $pivotPrinciple = $communicationDefender->principles()->first();
        $this->callFilamentAction(\App\Filament\Components\Principle\PrincipleTable::revokePrincipleButton($communicationDefender), $pivotPrinciple);
        $this->callFilamentAction(\App\Filament\Components\Principle\PrincipleTable::revokePrincipleBulkButton($communicationDefender), collect([$pivotPrinciple]));

        $this->callFilamentAction(\App\Filament\Components\Decision\DecisionForm::testRequestButton(), 'https://example.test', new class
        {
            public bool $failed = false;

            public function failure(): void
            {
                $this->failed = true;
            }
        });
        $this->callFilamentAction(\App\Filament\Components\Decision\DecisionTable::implementDecisionButton($communicationDefender), $communicationDecision->fresh());
        $this->callFilamentAction(\App\Filament\Components\Decision\DecisionTable::implementDecisionBulkButton($communicationDefender), collect([$communicationDecision->fresh()]));
        $pivotDecision = $communicationDefender->decisions()->first();
        $this->callFilamentAction(\App\Filament\Components\Decision\DecisionTable::suspendDecisionButton($communicationDefender), $pivotDecision);
        $this->callFilamentAction(\App\Filament\Components\Decision\DecisionTable::suspendDecisionBulkButton($communicationDefender), collect([$pivotDecision]));

        $report = Report::withoutEvents(fn () => Report::query()->create(['is_reviewed' => false]));
        $this->callFilamentAction(\App\Filament\Components\Report\ReportTable::reviewReportButton(), $report);
        $this->assertTrue($report->fresh()->is_reviewed);
        $this->callFilamentAction(\App\Filament\Components\Report\ReportTable::reviewReportBulkButton(), collect([Report::withoutEvents(fn () => Report::query()->create(['is_reviewed' => false]))]));

        $wordlist = $this->filamentWordlist();
        $this->callFilamentAction(\App\Filament\Components\Wordlist\WordlistTable::cloneWordlistButton(), $wordlist);

        $this->callFilamentAction(\App\Filament\Components\User\UserForm::generatePasswordButton(), fn (string $key, string $value) => $this->assertSame('password', $key));
        $this->callFilamentAction(\App\Filament\Components\Key\KeyForm::generateTokenButton(), fn (string $key, string $value) => $this->assertSame('token', $key));

        $this->callAdditionalSpecificButtonBranches($communicationDefender, $communicationPrinciple, $communicationDecision);
        $this->callAttachDetachLifecycleHooks($communicationDefender, $communicationPrinciple, $communicationDecision);
        $this->callGeneralButtonClosures();
    }
}
