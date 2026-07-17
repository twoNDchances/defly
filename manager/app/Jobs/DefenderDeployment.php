<?php

namespace App\Jobs;

use App\Enums\Defender\DeploymentStatus;
use App\Filament\Clusters\Infrastructure\Resources\Defenders\DefenderResource;
use App\Models\Defender;
use App\Services\DefenderEnvironment;
use App\Services\Notification;
use App\Services\Orchestrator;
use App\Services\Security;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\Response;
use Throwable;

class DefenderDeployment implements ShouldQueue
{
    use Queueable;

    public const ACTION_DEPLOY = 'deploy';

    public const ACTION_CANCEL = 'cancel';

    public function __construct(
        public string $defenderId,
        public string $action = self::ACTION_DEPLOY,
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
            return;
        }

        if ($this->normalizedAction() === self::ACTION_CANCEL) {
            $this->handleCancel($defender);

            return;
        }

        $this->handleDeploy($defender);
    }

    protected function handleDeploy(Defender $defender): void
    {
        if (! in_array($defender->deployment_status, [DeploymentStatus::Pending, DeploymentStatus::Processing], true)) {
            return;
        }

        if (! Security::requesterCanOperateDefender($defender, $this->requesterEmail)) {
            $this->markFailed($defender, [
                'detail' => __('notifications.defender.guard.denied'),
            ]);

            return;
        }

        $defender->forceFill([
            'deployment_status' => DeploymentStatus::Processing,
            'deployment_details' => ['detail' => __('notifications.defender.deployment.processing')],
            'environment_variables' => DefenderEnvironment::mergeDatabaseConnection(
                is_array($defender->environment_variables) ? $defender->environment_variables : [],
            ),
        ])->save();

        try {
            $response = Orchestrator::deploy(
                $defender->id,
                requesterEmail: $this->requesterEmail,
            );
            if ($response->successful()) {
                $defender->forceFill([
                    'deployment_status' => DeploymentStatus::Successful,
                    'deployment_details' => $this->responsePayload($response),
                ])->save();

                $this->notify(
                    $defender,
                    __('notifications.defender.deployment.completed.title'),
                    __('notifications.defender.deployment.completed.body', ['name' => $defender->name]),
                    Notification::STATUS_SUCCESS,
                );

                return;
            }

            $this->markFailed(
                $defender,
                [
                    'detail' => __('notifications.defender.deployment.request_failed'),
                    'status' => $response->status(),
                    'response' => $this->responsePayload($response),
                ],
            );
        } catch (Throwable $exception) {
            report($exception);

            $this->markFailed(
                $defender,
                [
                    'detail' => __('notifications.defender.deployment.exception'),
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ],
            );
        }
    }

    protected function handleCancel(Defender $defender): void
    {
        if (! in_array($defender->deployment_status, [DeploymentStatus::Successful, DeploymentStatus::Pending, DeploymentStatus::Processing], true)) {
            return;
        }

        if (! Security::requesterCanOperateDefender($defender, $this->requesterEmail)) {
            $this->markFailed($defender, [
                'detail' => __('notifications.defender.guard.denied'),
            ]);

            return;
        }

        try {
            $response = Orchestrator::cancel(
                $defender->id,
                requesterEmail: $this->requesterEmail,
            );
            if ($response->successful()) {
                $defender->forceFill([
                    'status' => null,
                    'details' => null,
                    'deployment_status' => null,
                    'deployment_details' => null,
                ])->save();

                $this->notify(
                    $defender,
                    __('notifications.defender.cancellation.completed.title'),
                    __('notifications.defender.cancellation.completed.body', ['name' => $defender->name]),
                    Notification::STATUS_SUCCESS,
                );

                return;
            }

            $this->markFailed(
                $defender,
                [
                    'detail' => __('notifications.defender.cancellation.request_failed'),
                    'status' => $response->status(),
                    'response' => $this->responsePayload($response),
                ],
            );
        } catch (Throwable $exception) {
            report($exception);

            $this->markFailed(
                $defender,
                [
                    'detail' => __('notifications.defender.cancellation.exception'),
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ],
            );
        }
    }

    protected function markFailed(Defender $defender, array $details): void
    {
        $defender->forceFill([
            'deployment_status' => DeploymentStatus::Failed,
            'deployment_details' => $details,
        ])->save();

        $this->notify(
            $defender,
            $this->normalizedAction() === self::ACTION_CANCEL
                ? __('notifications.defender.cancellation.failed.title')
                : __('notifications.defender.deployment.failed.title'),
            $this->failureBody($defender, $details),
            Notification::STATUS_DANGER,
        );
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

        return in_array($action, [self::ACTION_DEPLOY, self::ACTION_CANCEL], true)
            ? $action
            : self::ACTION_DEPLOY;
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

    protected function failureBody(Defender $defender, array $details): string
    {
        $detail = (string) ($details['detail'] ?? __('notifications.defender.failure.default_detail'));

        if (isset($details['status'])) {
            $detail .= ' '.__('notifications.defender.failure.http_status', ['status' => $details['status']]);
        }

        if (isset($details['message'])) {
            $detail .= " {$details['message']}";
        }

        return __('notifications.defender.failure.body', [
            'name' => $defender->name,
            'detail' => $detail,
        ]);
    }

    protected function useLocale(): void
    {
        if (filled($this->locale)) {
            app()->setLocale($this->locale);
        }
    }
}
