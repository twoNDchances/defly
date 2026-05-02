<?php

namespace App\Jobs;

use App\Enums\Defender\DeploymentStatus;
use App\Models\Defender;
use App\Services\Orchestrator;
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
    ) {}

    public function handle(): void
    {
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

        $defender->forceFill([
            'deployment_status' => DeploymentStatus::Processing,
            'deployment_details' => ['detail' => 'Deployment is being processed.'],
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

                return;
            }

            $this->markFailed(
                $defender,
                [
                    'detail' => 'Orchestrator deployment request failed.',
                    'status' => $response->status(),
                    'response' => $this->responsePayload($response),
                ],
            );
        } catch (Throwable $exception) {
            report($exception);

            $this->markFailed(
                $defender,
                [
                    'detail' => 'Unhandled exception while processing defender deployment.',
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

                return;
            }

            $this->markFailed(
                $defender,
                [
                    'detail' => 'Orchestrator cancel request failed.',
                    'status' => $response->status(),
                    'response' => $this->responsePayload($response),
                ],
            );
        } catch (Throwable $exception) {
            report($exception);

            $this->markFailed(
                $defender,
                [
                    'detail' => 'Unhandled exception while canceling defender.',
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
}
