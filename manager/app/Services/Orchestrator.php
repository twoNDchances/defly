<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Override;

class Orchestrator extends Connector
{
    public static function deploy(
        string $defenderId,
        array $data = [],
        ?string $requesterEmail = null,
    ): Response
    {
        return static::send(
            static::deploymentPath($defenderId),
            config('customization.backend.apis.orchestrator.paths.deployment.methods.deploy', 'post'),
            $data,
            headers: static::emailHeader($requesterEmail),
        );
    }

    public static function follow(
        string $defenderId,
        array $query = [],
        ?string $requesterEmail = null,
    ): Response
    {
        return static::send(
            static::deploymentPath($defenderId),
            config('customization.backend.apis.orchestrator.paths.deployment.methods.follow', 'get'),
            query: $query,
            headers: static::emailHeader($requesterEmail),
        );
    }

    public static function cancel(
        string $defenderId,
        array $data = [],
        ?string $requesterEmail = null,
    ): Response
    {
        return static::send(
            static::deploymentPath($defenderId),
            config('customization.backend.apis.orchestrator.paths.deployment.methods.cancel', 'delete'),
            $data,
            headers: static::emailHeader($requesterEmail),
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

    #[Override]
    protected static function requestOptions(): array
    {
        $skipVerify = (bool) config(
            'customization.backend.apis.orchestrator.tls.skip_verify',
            false,
        );
        if ($skipVerify) {
            return ['verify' => false];
        }

        $configuredPath = (string) config(
            'customization.backend.apis.orchestrator.tls.cert_file',
            'storage/tls/orchestrator/orchestrator.crt',
        );
        $certificatePath = trim($configuredPath);
        if ($certificatePath === '') {
            $certificatePath = 'storage/tls/orchestrator/orchestrator.crt';
        }

        if (! str_starts_with($certificatePath, DIRECTORY_SEPARATOR) && ! preg_match('/^[A-Za-z]:\\\\/', $certificatePath)) {
            $certificatePath = base_path($certificatePath);
        }

        return ['verify' => $certificatePath];
    }

    #[Override]
    protected static function requestHeaders(): array
    {
        $headerName = trim((string) config(
            'customization.backend.apis.orchestrator.headers.email_header_key',
            'X-Executor',
        ));
        $email = trim((string) Identification::getEmail());

        if (($headerName === '') || ($email === '')) {
            return [];
        }

        return [$headerName => $email];
    }

    protected static function emailHeader(?string $requesterEmail = null): array
    {
        $headerName = trim((string) config(
            'customization.backend.apis.orchestrator.headers.email_header_key',
            'X-Executor',
        ));
        if ($headerName === '') {
            return [];
        }

        $email = trim((string) ($requesterEmail ?? ''));
        if ($email === '') {
            return static::requestHeaders();
        }

        return [$headerName => $email];
    }
}
