<?php

namespace App\Jobs;

use App\Enums\Defender\DeploymentStatus;
use App\Filament\Resources\Defenders\DefenderResource;
use App\Models\Decision;
use App\Models\Defender;
use App\Models\Principle;
use App\Services\Defender as DefenderService;
use App\Services\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class DefenderCommunication implements ShouldQueue
{
    use Queueable;

    public const ACTION_APPLY = 'apply';

    public const ACTION_REVOKE = 'revoke';

    public const ACTION_IMPLEMENT = 'implement';

    public const ACTION_SUSPEND = 'suspend';

    public function __construct(
        public string $defenderId,
        public array $resourceIds,
        public string $action = self::ACTION_APPLY,
        public ?string $requesterEmail = null,
        public ?string $locale = null,
    ) {
        $this->locale ??= app()->getLocale();
    }

    public function handle(): void
    {
        $this->useLocale();

        $defender = Defender::query()->find($this->defenderId);
        if (! $defender) {
            Notification::sendToRequester(
                requesterEmail: $this->requesterEmail,
                title: __('notifications.defender.action.skipped.title'),
                body: __('notifications.defender.action.skipped.missing'),
                status: Notification::STATUS_WARNING,
            );

            return;
        }

        if ($defender->deployment_status !== DeploymentStatus::Successful) {
            $this->notify(
                $defender,
                __('notifications.defender.action.skipped.title'),
                __('notifications.defender.action.skipped.not_deployed', ['name' => $defender->name]),
                Notification::STATUS_WARNING,
            );

            return;
        }

        $resourceIds = $this->normalizedResourceIds();
        if ($resourceIds === []) {
            $this->notify(
                $defender,
                __('notifications.defender.action.skipped.title'),
                __('notifications.defender.action.skipped.empty'),
                Notification::STATUS_WARNING,
            );

            return;
        }

        match ($this->normalizedAction()) {
            self::ACTION_REVOKE => $this->handlePrinciples($defender, $resourceIds, false),
            self::ACTION_IMPLEMENT => $this->handleDecisions($defender, $resourceIds, true),
            self::ACTION_SUSPEND => $this->handleDecisions($defender, $resourceIds, false),
            default => $this->handlePrinciples($defender, $resourceIds, true),
        };
    }

    protected function handlePrinciples(Defender $defender, array $resourceIds, bool $isApply): void
    {
        $principleIds = $this->attachedPrincipleIds($defender, $resourceIds);
        if ($principleIds === []) {
            $this->notify(
                $defender,
                __('notifications.principle.action.skipped.title'),
                __('notifications.principle.action.skipped.not_attached', ['name' => $defender->name]),
                Notification::STATUS_WARNING,
            );

            return;
        }

        try {
            $response = $isApply
                ? DefenderService::apply($defender, $principleIds, requesterEmail: $this->requesterEmail)
                : DefenderService::revoke($defender, $principleIds, requesterEmail: $this->requesterEmail);

            if ($response->successful()) {
                $this->saveLastResponseDetails($defender, 'principle', $response, $principleIds, true);

                $this->updatePrinciplePivots($defender, $principleIds, $isApply);
                $this->notifyCommunicationResult($defender, 'principle', $principleIds, true, $response);

                return;
            }

            $this->saveLastResponseDetails($defender, 'principle', $response, $principleIds, false);
            $this->logFailedResponse($defender, $response);
            $this->notifyCommunicationResult($defender, 'principle', $principleIds, false, $response);
        } catch (Throwable $exception) {
            $this->saveExceptionDetails($defender, 'principle', $principleIds, $exception);
            $this->notifyCommunicationException($defender, 'principle', $principleIds, $exception);
            report($exception);
        }
    }

    protected function handleDecisions(Defender $defender, array $resourceIds, bool $isImplement): void
    {
        $decisionIds = $this->attachedDecisionIds($defender, $resourceIds);
        if ($decisionIds === []) {
            $this->notify(
                $defender,
                __('notifications.decision.action.skipped.title'),
                __('notifications.decision.action.skipped.not_attached', ['name' => $defender->name]),
                Notification::STATUS_WARNING,
            );

            return;
        }

        try {
            $response = $isImplement
                ? DefenderService::implement($defender, $decisionIds, requesterEmail: $this->requesterEmail)
                : DefenderService::suspend($defender, $decisionIds, requesterEmail: $this->requesterEmail);

            if ($response->successful()) {
                $this->saveLastResponseDetails($defender, 'decision', $response, $decisionIds, true);

                $this->updateDecisionPivots($defender, $decisionIds, $isImplement);
                $this->notifyCommunicationResult($defender, 'decision', $decisionIds, true, $response);

                return;
            }

            $this->saveLastResponseDetails($defender, 'decision', $response, $decisionIds, false);
            $this->logFailedResponse($defender, $response);
            $this->notifyCommunicationResult($defender, 'decision', $decisionIds, false, $response);
        } catch (Throwable $exception) {
            $this->saveExceptionDetails($defender, 'decision', $decisionIds, $exception);
            $this->notifyCommunicationException($defender, 'decision', $decisionIds, $exception);
            report($exception);
        }
    }

    protected function attachedPrincipleIds(Defender $defender, array $resourceIds): array
    {
        return Principle::query()
            ->whereKey($resourceIds)
            ->whereHas('defenders', fn ($query) => $query->whereKey($defender->id))
            ->pluck('id')
            ->all();
    }

    protected function attachedDecisionIds(Defender $defender, array $resourceIds): array
    {
        return Decision::query()
            ->whereKey($resourceIds)
            ->whereHas('defenders', fn ($query) => $query->whereKey($defender->id))
            ->pluck('id')
            ->all();
    }

    protected function updatePrinciplePivots(Defender $defender, array $principleIds, bool $isApplied): void
    {
        foreach ($principleIds as $principleId) {
            $defender->principles()->updateExistingPivot($principleId, [
                'is_applied' => $isApplied,
            ]);
        }
    }

    protected function updateDecisionPivots(Defender $defender, array $decisionIds, bool $isImplemented): void
    {
        foreach ($decisionIds as $decisionId) {
            $defender->decisions()->updateExistingPivot($decisionId, [
                'is_implemented' => $isImplemented,
            ]);
        }
    }

    protected function logFailedResponse(Defender $defender, Response $response): void
    {
        Log::warning('Defender communication request failed.', [
            'defender_id' => $defender->id,
            'action' => $this->normalizedAction(),
            'status' => $response->status(),
            'response' => $this->responsePayload($response),
        ]);
    }

    protected function saveLastResponseDetails(
        Defender $defender,
        string $type,
        Response $response,
        array $resourceIds,
        bool $successful,
    ): void {
        $details = $defender->fresh()?->last_response_details ?? [];
        $details[$type] = [
            'status' => $successful ? 'successful' : 'failed',
            'action' => $this->normalizedAction(),
            'resource_ids' => array_values($resourceIds),
            'requester_email' => $this->requesterEmail,
            'http_status' => $response->status(),
            'response' => $this->responsePayload($response),
            'responded_at' => now()->toIso8601String(),
        ];

        $defender->forceFill([
            'last_response_details' => $details,
        ])->save();
    }

    protected function saveExceptionDetails(Defender $defender, string $type, array $resourceIds, Throwable $exception): void
    {
        $details = $defender->fresh()?->last_response_details ?? [];
        $details[$type] = [
            'status' => 'failed',
            'action' => $this->normalizedAction(),
            'resource_ids' => array_values($resourceIds),
            'requester_email' => $this->requesterEmail,
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
            'responded_at' => now()->toIso8601String(),
        ];

        $defender->forceFill([
            'last_response_details' => $details,
        ])->save();
    }

    protected function responsePayload(Response $response): array
    {
        $json = $response->json();
        if (is_array($json)) {
            return $json;
        }

        return [
            'body' => $response->body(),
        ];
    }

    protected function normalizedAction(): string
    {
        $action = strtolower(trim($this->action));

        return in_array($action, [
            self::ACTION_APPLY,
            self::ACTION_REVOKE,
            self::ACTION_IMPLEMENT,
            self::ACTION_SUSPEND,
        ], true)
            ? $action
            : self::ACTION_APPLY;
    }

    protected function normalizedResourceIds(): array
    {
        return array_values(array_unique(array_filter(
            array_map(fn (mixed $id): string => trim((string) $id), $this->resourceIds),
            fn (string $id): bool => $id !== '',
        )));
    }

    protected function notifyCommunicationResult(
        Defender $defender,
        string $resourceType,
        array $resourceIds,
        bool $successful,
        Response $response,
    ): void {
        $this->notify(
            $defender,
            $this->communicationTitle($resourceType, $successful),
            $this->communicationBody(
                $defender,
                $resourceType,
                $resourceIds,
                $successful ? 'successful' : 'failed',
                ['status' => $response->status()],
            ),
            $successful ? Notification::STATUS_SUCCESS : Notification::STATUS_DANGER,
        );
    }

    protected function notifyCommunicationException(
        Defender $defender,
        string $resourceType,
        array $resourceIds,
        Throwable $exception,
    ): void {
        $this->notify(
            $defender,
            $this->communicationTitle($resourceType, false),
            $this->communicationBody(
                $defender,
                $resourceType,
                $resourceIds,
                'exception',
                ['message' => $exception->getMessage()],
            ),
            Notification::STATUS_DANGER,
        );
    }

    protected function communicationTitle(string $resourceType, bool $successful): string
    {
        return __(
            "notifications.communication.titles.{$resourceType}.{$this->normalizedAction()}."
            . ($successful ? 'completed' : 'failed')
        );
    }

    protected function communicationBody(
        Defender $defender,
        string $resourceType,
        array $resourceIds,
        string $result,
        array $extra = [],
    ): string {
        $count = count($resourceIds);

        return __("notifications.communication.result_body.{$result}", [
            'action' => __("notifications.communication.actions.{$this->normalizedAction()}"),
            'count' => $count,
            'resource' => trans_choice("notifications.communication.resources.{$resourceType}", $count),
            'defender' => $defender->name,
            ...$extra,
        ]);
    }

    protected function notify(Defender $defender, string $title, ?string $body, string $status): void
    {
        Notification::sendForRecord(
            requesterEmail: $this->requesterEmail,
            record: $defender,
            title: $title,
            body: $body,
            status: $status,
            url: Notification::resourceUrl(DefenderResource::class, $defender),
            urlLabel: __('notifications.actions.view_defender'),
        );
    }

    protected function useLocale(): void
    {
        if (filled($this->locale)) {
            app()->setLocale($this->locale);
        }
    }
}
