<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Override;

class Orchestrator extends Connector
{
    public static function deploy(string $defenderId, array $data = []): Response
    {
        return static::send(
            static::deploymentPath($defenderId),
            config('customization.backend.apis.orchestrator.paths.deployment.methods.deploy', 'post'),
            $data,
        );
    }

    public static function follow(string $defenderId, array $query = []): Response
    {
        return static::send(
            static::deploymentPath($defenderId),
            config('customization.backend.apis.orchestrator.paths.deployment.methods.follow', 'get'),
            query: $query,
        );
    }

    public static function cancel(string $defenderId, array $data = []): Response
    {
        return static::send(
            static::deploymentPath($defenderId),
            config('customization.backend.apis.orchestrator.paths.deployment.methods.cancel', 'delete'),
            $data,
        );
    }

    protected static function deploymentPath(string $defenderId): string
    {
        return trim((string) config('customization.backend.apis.orchestrator.paths.deployment.path', 'deployments'), '/')
            .'/'
            .trim($defenderId, '/');
    }

    #[Override]
    protected static function baseUrl(): ?string
    {
        return config('customization.backend.apis.orchestrator.base_url');
    }

    #[Override]
    protected static function pathPrefix(): ?string
    {
        return config('customization.backend.apis.orchestrator.paths.prefix');
    }

    #[Override]
    protected static function username(): ?string
    {
        return config('customization.backend.apis.orchestrator.credentials.username');
    }

    #[Override]
    protected static function password(): ?string
    {
        return config('customization.backend.apis.orchestrator.credentials.password');
    }
}
