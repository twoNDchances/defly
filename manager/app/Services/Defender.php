<?php

namespace App\Services;

use App\Models\Defender as DefenderModel;
use Illuminate\Http\Client\Response;
use Override;

class Defender extends Connector
{
    public static function apply(
        DefenderModel $defender,
        array $principleIds,
        array $data = [],
        ?string $requesterEmail = null,
    ): Response {
        return static::sendToDefender(
            $defender,
            static::principlePath(),
            config('customization.backend.apis.defender.paths.principle.methods.apply', 'put'),
            $data !== [] ? $data : static::principlesPayload($principleIds),
            requesterEmail: $requesterEmail,
        );
    }

    public static function revoke(
        DefenderModel $defender,
        array $principleIds,
        array $data = [],
        ?string $requesterEmail = null,
    ): Response {
        return static::sendToDefender(
            $defender,
            static::principlePath(),
            config('customization.backend.apis.defender.paths.principle.methods.revoke', 'delete'),
            $data !== [] ? $data : static::principlesPayload($principleIds),
            requesterEmail: $requesterEmail,
        );
    }

    public static function implement(
        DefenderModel $defender,
        array $decisionIds,
        array $data = [],
        ?string $requesterEmail = null,
    ): Response {
        return static::sendToDefender(
            $defender,
            static::decisionPath(),
            config('customization.backend.apis.defender.paths.decision.methods.implement', 'put'),
            $data !== [] ? $data : static::decisionsPayload($decisionIds),
            requesterEmail: $requesterEmail,
        );
    }

    public static function suspend(
        DefenderModel $defender,
        array $decisionIds,
        array $data = [],
        ?string $requesterEmail = null,
    ): Response {
        return static::sendToDefender(
            $defender,
            static::decisionPath(),
            config('customization.backend.apis.defender.paths.decision.methods.suspend', 'delete'),
            $data !== [] ? $data : static::decisionsPayload($decisionIds),
            requesterEmail: $requesterEmail,
        );
    }

    protected static function sendToDefender(
        DefenderModel $defender,
        string $path,
        string $method,
        array $data = [],
        array $query = [],
        ?string $requesterEmail = null,
    ): Response {
        return static::sendRequest(
            static::request(static::emailHeader($requesterEmail))
                ->withOptions(static::requestOptionsFor($defender)),
            $path,
            $method,
            $data,
            $query,
        );
    }

    protected static function principlePath(): string
    {
        return trim((string) config('customization.backend.apis.defender.paths.principle.path', 'principles'), '/');
    }

    protected static function decisionPath(): string
    {
        return trim((string) config('customization.backend.apis.defender.paths.decision.path', 'decisions'), '/');
    }

    protected static function principlesPayload(array $principleIds): array
    {
        return [
            'principle_ids' => static::normalizeIds($principleIds),
        ];
    }

    protected static function decisionsPayload(array $decisionIds): array
    {
        return [
            'decision_ids' => static::normalizeIds($decisionIds),
        ];
    }

    protected static function normalizeIds(array $ids): array
    {
        return array_values(array_unique(array_filter(
            array_map(fn (mixed $id): string => trim((string) $id), $ids),
            fn (string $id): bool => $id !== '',
        )));
    }

    #[Override]
    protected static function baseUrl(): ?string
    {
        return config('customization.backend.apis.defender.base_url');
    }

    #[Override]
    protected static function pathPrefix(): ?string
    {
        return config('customization.backend.apis.defender.paths.prefix');
    }

    #[Override]
    protected static function username(): ?string
    {
        return config('customization.backend.apis.defender.credentials.username');
    }

    #[Override]
    protected static function password(): ?string
    {
        return config('customization.backend.apis.defender.credentials.password');
    }

    protected static function requestOptionsFor(DefenderModel $defender): array
    {
        $skipVerify = (bool) config(
            'customization.backend.apis.defender.tls.skip_verify',
            false,
        );
        if ($skipVerify) {
            return ['verify' => false];
        }

        $defenderName = trim((string) $defender->name);
        if ($defenderName === '') {
            return [];
        }

        return ['verify' => static::certificatePath($defenderName)];
    }

    #[Override]
    protected static function requestHeaders(): array
    {
        $headerName = static::emailHeaderName();
        $email = trim((string) Identification::getEmail());

        if (($headerName === '') || ($email === '')) {
            return [];
        }

        return [$headerName => $email];
    }

    protected static function emailHeader(?string $requesterEmail = null): array
    {
        $headerName = static::emailHeaderName();
        if ($headerName === '') {
            return [];
        }

        $email = trim((string) ($requesterEmail ?? ''));
        if ($email === '') {
            return static::requestHeaders();
        }

        return [$headerName => $email];
    }

    protected static function emailHeaderName(): string
    {
        return trim((string) config(
            'customization.backend.apis.defender.headers.email_header_key',
            'X-Executor',
        ));
    }

    protected static function certificatePath(string $defenderName): string
    {
        $directory = trim((string) config(
            'customization.backend.apis.defender.tls.directory',
            'storage/tls/defenders',
        ));
        if ($directory === '') {
            $directory = 'storage/tls/defenders';
        }

        $certificatePath = rtrim($directory, '\\/')
            .DIRECTORY_SEPARATOR
            .$defenderName
            .'.crt';

        if (! static::isAbsolutePath($certificatePath)) {
            $certificatePath = base_path($certificatePath);
        }

        return $certificatePath;
    }

    protected static function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR)
            || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1;
    }
}
