<?php

namespace App\Http\Controllers;

use App\Enums\Defender\DeploymentStatus;
use App\Enums\Principle\ValidationStatus;
use App\Http\Requests\DefenderActionRequest;
use App\Http\Requests\DefenderDecisionActionRequest;
use App\Http\Requests\DefenderPrincipleActionRequest;
use App\Http\Requests\DefenderRequest;
use App\Http\Requests\DefenderRelationRequest;
use App\Jobs\DefenderCommunication;
use App\Jobs\DefenderDeployment;
use App\Models\Decision;
use App\Models\Defender;
use App\Models\Principle;
use App\Services\ApiPayload;
use App\Services\Identification;
use App\Services\Logger;
use App\Services\Orchestrator;
use App\Traits\Filament\Specifics\Defender\DefenderData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class DefenderController extends Controller
{
    use DefenderData;

    public function index(DefenderRequest $request): JsonResponse
    {
        $defenders = Defender::query()
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($defenders);
    }

    public function store(DefenderRequest $request): JsonResponse
    {
        $defender = Defender::query()->create($this->defenderData($request));

        return response()->json($defender, SymfonyResponse::HTTP_CREATED);
    }

    public function payload(DefenderRequest $request): JsonResponse
    {
        return response()->json(ApiPayload::resource('defenders', [
            'store' => [
                'method' => 'POST',
                'body' => [
                    'name' => 'primary-defender',
                    'proxy_port' => 9948,
                    'environment_variables' => $this->payloadEnvironmentVariables(),
                    'description' => 'Defender API example.',
                ],
            ],
            'update' => [
                'method' => 'PATCH',
                'path' => '{defender}',
                'body' => [
                    'description' => 'Updated defender description.',
                ],
            ],
            'deploy' => [
                'method' => 'POST',
                'path' => '{defender}/deploy',
            ],
            'cancel' => [
                'method' => 'POST',
                'path' => '{defender}/cancel',
            ],
            'follow' => [
                'method' => 'POST',
                'path' => '{defender}/follow',
            ],
            'list_principles' => [
                'method' => 'GET',
                'path' => '{defender}/principles',
            ],
            'attach_principles' => [
                'method' => 'POST',
                'path' => '{defender}/principles',
                'body' => [
                    'ids' => [
                        '<principle-id-1>',
                        '<principle-id-2>',
                    ],
                ],
            ],
            'detach_principles' => [
                'method' => 'DELETE',
                'path' => '{defender}/principles',
                'body' => [
                    'ids' => [
                        '<principle-id-1>',
                    ],
                ],
            ],
            'apply_principle' => [
                'method' => 'POST',
                'path' => '{defender}/principles/{principle}/apply',
            ],
            'revoke_principle' => [
                'method' => 'POST',
                'path' => '{defender}/principles/{principle}/revoke',
            ],
            'list_decisions' => [
                'method' => 'GET',
                'path' => '{defender}/decisions',
            ],
            'attach_decisions' => [
                'method' => 'POST',
                'path' => '{defender}/decisions',
                'body' => [
                    'ids' => [
                        '<decision-id-1>',
                        '<decision-id-2>',
                    ],
                ],
            ],
            'detach_decisions' => [
                'method' => 'DELETE',
                'path' => '{defender}/decisions',
                'body' => [
                    'ids' => [
                        '<decision-id-1>',
                    ],
                ],
            ],
            'implement_decision' => [
                'method' => 'POST',
                'path' => '{defender}/decisions/{decision}/implement',
            ],
            'suspend_decision' => [
                'method' => 'POST',
                'path' => '{defender}/decisions/{decision}/suspend',
            ],
            'list_labels' => [
                'method' => 'GET',
                'path' => '{defender}/labels',
            ],
            'attach_labels' => [
                'method' => 'POST',
                'path' => '{defender}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                        '<label-id-2>',
                    ],
                ],
            ],
            'detach_labels' => [
                'method' => 'DELETE',
                'path' => '{defender}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                    ],
                ],
            ],
        ]));
    }

    public function show(DefenderRequest $request, Defender $defender): JsonResponse
    {
        return response()->json($defender);
    }

    public function update(DefenderRequest $request, Defender $defender): JsonResponse
    {
        $defender->update($this->defenderData($request));

        return response()->json($defender->refresh());
    }

    public function destroy(DefenderRequest $request, Defender $defender): HttpResponse
    {
        $defender->delete();

        return response()->noContent();
    }

    public function deploy(DefenderActionRequest $request, Defender $defender): JsonResponse
    {
        if (in_array($defender->deployment_status, [DeploymentStatus::Pending, DeploymentStatus::Processing], true)) {
            return response()->json($defender);
        }

        $defender->deployment_status = DeploymentStatus::Pending;
        $defender->save();

        DefenderDeployment::dispatch(
            $defender->id,
            DefenderDeployment::ACTION_DEPLOY,
            Identification::getEmail(),
        );
        Logger::log($defender, 'deploy');

        return response()->json($defender->refresh());
    }

    public function cancel(DefenderActionRequest $request, Defender $defender): JsonResponse
    {
        if ($defender->deployment_status !== DeploymentStatus::Successful) {
            return response()->json($defender);
        }

        $defender->forceFill([
            'deployment_status' => DeploymentStatus::Pending,
            'deployment_details' => ['detail' => __('notifications.defender.cancellation.queued')],
        ])->save();

        DefenderDeployment::dispatch(
            $defender->id,
            DefenderDeployment::ACTION_CANCEL,
            Identification::getEmail(),
        );
        Logger::log($defender, 'cancel');

        return response()->json($defender->refresh());
    }

    public function follow(DefenderActionRequest $request, Defender $defender): JsonResponse
    {
        $state = '';

        try {
            $response = Orchestrator::follow(
                (string) $defender->getKey(),
                requesterEmail: Identification::getEmail(),
            );
            $state = $response->json();

            if ($state === null) {
                $state = [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ];
            }
        } catch (Throwable $exception) {
            $state = [
                'detail' => __('forms.defender.extras.log.failed_to_follow'),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ];
        }

        if (! is_string($state)) {
            $state = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                ?: (string) print_r($state, true);
        }

        Logger::log($defender, 'follow');

        return response()->json([
            'log' => $state,
        ]);
    }

    public function principles(DefenderRelationRequest $request, Defender $defender): JsonResponse
    {
        return response()->json($defender->principles()
            ->where('validation_status', ValidationStatus::Passed->value)
            ->latest()
            ->get());
    }

    public function attachPrinciples(DefenderRelationRequest $request, Defender $defender): JsonResponse
    {
        $ids = $request->validated('ids', []);
        $relation = $defender->principles();
        $relation->syncWithoutDetaching($ids);
        $this->syncRelationLocks($relation->getRelated()::class, $ids);

        return response()->json($defender->principles()
            ->where('validation_status', ValidationStatus::Passed->value)
            ->latest()
            ->get());
    }

    public function detachPrinciples(DefenderRelationRequest $request, Defender $defender): JsonResponse
    {
        $ids = $request->validated('ids', []);
        $relation = $defender->principles();
        $relation->detach($ids);
        $this->syncRelationLocks($relation->getRelated()::class, $ids);

        return response()->json($defender->principles()
            ->where('validation_status', ValidationStatus::Passed->value)
            ->latest()
            ->get());
    }

    public function applyPrinciple(
        DefenderPrincipleActionRequest $request,
        Defender $defender,
        Principle $principle,
    ): JsonResponse {
        $attachedPrinciple = $this->defenderPrinciple($defender, $principle);
        if ($defender->deployment_status !== DeploymentStatus::Successful
            || $principle->validation_status !== ValidationStatus::Passed
            || ! $attachedPrinciple) {
            return response()->json($attachedPrinciple ?? $principle);
        }

        DefenderCommunication::dispatch(
            $defender->id,
            [$principle->id],
            DefenderCommunication::ACTION_APPLY,
            Identification::getEmail(),
        );
        Logger::log($principle, 'apply');

        return response()->json($attachedPrinciple);
    }

    public function revokePrinciple(
        DefenderPrincipleActionRequest $request,
        Defender $defender,
        Principle $principle,
    ): JsonResponse {
        $attachedPrinciple = $this->defenderPrinciple($defender, $principle);
        if ($defender->deployment_status !== DeploymentStatus::Successful
            || $principle->validation_status !== ValidationStatus::Passed
            || ! $attachedPrinciple
            || ! (bool) data_get($attachedPrinciple, 'pivot.is_applied', false)) {
            return response()->json($attachedPrinciple ?? $principle);
        }

        DefenderCommunication::dispatch(
            $defender->id,
            [$principle->id],
            DefenderCommunication::ACTION_REVOKE,
            Identification::getEmail(),
        );
        Logger::log($principle, 'revoke');

        return response()->json($attachedPrinciple);
    }

    public function decisions(DefenderRelationRequest $request, Defender $defender): JsonResponse
    {
        return response()->json($defender->decisions()
            ->latest()
            ->get());
    }

    public function attachDecisions(DefenderRelationRequest $request, Defender $defender): JsonResponse
    {
        $ids = $request->validated('ids', []);
        $relation = $defender->decisions();
        $relation->syncWithoutDetaching($ids);
        $this->syncRelationLocks($relation->getRelated()::class, $ids);

        return response()->json($defender->decisions()
            ->latest()
            ->get());
    }

    public function detachDecisions(DefenderRelationRequest $request, Defender $defender): JsonResponse
    {
        $ids = $request->validated('ids', []);
        $relation = $defender->decisions();
        $relation->detach($ids);
        $this->syncRelationLocks($relation->getRelated()::class, $ids);

        return response()->json($defender->decisions()
            ->latest()
            ->get());
    }

    public function implementDecision(
        DefenderDecisionActionRequest $request,
        Defender $defender,
        Decision $decision,
    ): JsonResponse {
        $attachedDecision = $this->defenderDecision($defender, $decision);
        if ($defender->deployment_status !== DeploymentStatus::Successful || ! $attachedDecision) {
            return response()->json($attachedDecision ?? $decision);
        }

        DefenderCommunication::dispatch(
            $defender->id,
            [$decision->id],
            DefenderCommunication::ACTION_IMPLEMENT,
            Identification::getEmail(),
        );
        Logger::log($decision, 'implement');

        return response()->json($attachedDecision);
    }

    public function suspendDecision(
        DefenderDecisionActionRequest $request,
        Defender $defender,
        Decision $decision,
    ): JsonResponse {
        $attachedDecision = $this->defenderDecision($defender, $decision);
        if ($defender->deployment_status !== DeploymentStatus::Successful
            || ! $attachedDecision
            || ! (bool) data_get($attachedDecision, 'pivot.is_implemented', false)) {
            return response()->json($attachedDecision ?? $decision);
        }

        DefenderCommunication::dispatch(
            $defender->id,
            [$decision->id],
            DefenderCommunication::ACTION_SUSPEND,
            Identification::getEmail(),
        );
        Logger::log($decision, 'suspend');

        return response()->json($attachedDecision);
    }

    public function labels(DefenderRelationRequest $request, Defender $defender): JsonResponse
    {
        return response()->json($defender->labels()
            ->latest()
            ->get());
    }

    public function attachLabels(DefenderRelationRequest $request, Defender $defender): JsonResponse
    {
        $defender->labels()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($defender->labels()
            ->latest()
            ->get());
    }

    public function detachLabels(DefenderRelationRequest $request, Defender $defender): JsonResponse
    {
        $defender->labels()->detach($request->validated('ids', []));

        return response()->json($defender->labels()
            ->latest()
            ->get());
    }

    private function defenderData(DefenderRequest $request): array
    {
        $data = self::saveForm($request->validated());

        return $this->onlyFields($data, [
            'name',
            'proxy_port',
            'environment_variables',
            'description',
        ]);
    }

    private function defaultEnvironmentVariables(): array
    {
        return [
            ...self::environmentVariablesToMap(self::commonEnvironmentVariables()),
            ...self::environmentVariablesToMap(self::serverEnvironmentVariables()),
            ...self::environmentVariablesToMap(self::proxyEnvironmentVariables()),
        ];
    }

    private function payloadEnvironmentVariables(): array
    {
        return array_replace($this->defaultEnvironmentVariables(), [
            'DATABASE_HOST' => '<database-host>',
            'DATABASE_PORT' => '<database-port>',
            'DATABASE_NAME' => '<database-name>',
            'DATABASE_USER' => '<database-user>',
            'DATABASE_PASS' => '<database-password>',
            'SERVER_SECURITY_PASSWORD' => '<defender-password>',
        ]);
    }

    private function defenderPrinciple(Defender $defender, Principle $principle): ?Principle
    {
        return $defender->principles()
            ->whereKey($principle->getKey())
            ->first();
    }

    private function defenderDecision(Defender $defender, Decision $decision): ?Decision
    {
        return $defender->decisions()
            ->whereKey($decision->getKey())
            ->first();
    }
}
