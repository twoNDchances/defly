<?php

namespace App\Services;

use App\Models\Defender as DefenderModel;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

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
            static::principlePath($defender),
            static::methodFrom($defender, 'SERVER_CONTROLLER_METHOD_APPLY', 'put'),
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
            static::principlePath($defender),
            static::methodFrom($defender, 'SERVER_CONTROLLER_METHOD_REVOKE', 'delete'),
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
            static::decisionPath($defender),
            static::methodFrom($defender, 'SERVER_CONTROLLER_METHOD_IMPLEMENT', 'put'),
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
            static::decisionPath($defender),
            static::methodFrom($defender, 'SERVER_CONTROLLER_METHOD_SUSPEND', 'delete'),
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
            static::requestFor($defender, static::emailHeader($defender, $requesterEmail))
                ->withOptions(static::requestOptionsFor($defender)),
            $path,
            $method,
            $data,
            $query,
        );
    }

    protected static function principlePath(DefenderModel $defender): string
    {
        return static::pathFrom($defender, 'SERVER_CONTROLLER_PATH_PRINCIPLES', 'principles');
    }

    protected static function decisionPath(DefenderModel $defender): string
    {
        return static::pathFrom($defender, 'SERVER_CONTROLLER_PATH_DECISIONS', 'decisions');
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

    protected static function requestFor(DefenderModel $defender, array $headers = []): PendingRequest
    {
        $request = Http::baseUrl(static::baseUriFor($defender))
            ->acceptJson()
            ->asJson();

        $requestHeaders = array_merge(static::requestHeadersFor($defender), $headers);
        if ($requestHeaders !== []) {
            $request = $request->withHeaders($requestHeaders);
        }

        $username = static::environmentValue($defender, 'SERVER_SECURITY_USERNAME', 'defly-defender');
        $password = static::environmentValue($defender, 'SERVER_SECURITY_PASSWORD', 'P@55w0rd');
        if (filled($username) || filled($password)) {
            $request->withBasicAuth($username, $password);
        }

        return $request;
    }

    protected static function baseUriFor(DefenderModel $defender): string
    {
        $scheme = static::boolFrom($defender, 'SERVER_HTTPS_ENABLE', true) ? 'https' : 'http';
        $host = trim((string) $defender->name);
        if ($host === '') {
            $host = 'defender';
        }

        $port = static::portFrom($defender, 'SERVER_PORT', '9947');
        $pathPrefix = static::pathFrom($defender, 'SERVER_CONTROLLER_PATH_PREFIX', 'api/v1');
        $baseUrl = "{$scheme}://{$host}:{$port}";

        return $pathPrefix === '' ? $baseUrl : "{$baseUrl}/{$pathPrefix}";
    }

    protected static function pathFrom(DefenderModel $defender, string $key, string $fallback): string
    {
        return trim(static::environmentValue($defender, $key, $fallback), '/');
    }

    protected static function methodFrom(DefenderModel $defender, string $key, string $fallback): string
    {
        $method = strtolower(static::environmentValue($defender, $key, $fallback));

        return in_array($method, ['post', 'put', 'patch', 'delete'], true)
            ? $method
            : strtolower($fallback);
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

    protected static function requestHeadersFor(DefenderModel $defender): array
    {
        $headerName = static::emailHeaderName($defender);
        $email = trim((string) Identification::getEmail());

        if (($headerName === '') || ($email === '')) {
            return [];
        }

        return [$headerName => $email];
    }

    protected static function emailHeader(DefenderModel $defender, ?string $requesterEmail = null): array
    {
        $headerName = static::emailHeaderName($defender);
        if ($headerName === '') {
            return [];
        }

        $email = trim((string) ($requesterEmail ?? ''));
        if ($email === '') {
            return static::requestHeadersFor($defender);
        }

        return [$headerName => $email];
    }

    protected static function emailHeaderName(DefenderModel $defender): string
    {
        return static::environmentValue($defender, 'SERVER_CONTROLLER_AUTHORIZATION_EMAIL', 'X-Executor');
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

    protected static function environmentValue(DefenderModel $defender, string $key, string $fallback = ''): string
    {
        $variables = $defender->environment_variables;
        if (! is_array($variables)) {
            return $fallback;
        }

        if (array_key_exists($key, $variables) && (! is_array($variables[$key]))) {
            return trim((string) $variables[$key]);
        }

        foreach ($variables as $item) {
            if (! is_array($item) || (($item['key'] ?? null) !== $key)) {
                continue;
            }

            $value = $item['value'] ?? $fallback;

            return is_array($value)
                ? $fallback
                : trim((string) $value);
        }

        return $fallback;
    }

    protected static function boolFrom(DefenderModel $defender, string $key, bool $fallback): bool
    {
        $value = strtolower(static::environmentValue($defender, $key, $fallback ? 'true' : 'false'));

        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    protected static function portFrom(DefenderModel $defender, string $key, string $fallback): string
    {
        $port = static::environmentValue($defender, $key, $fallback);
        $number = (int) $port;

        if (($number < 1) || ($number > 65535)) {
            return $fallback;
        }

        return (string) $number;
    }

    protected static function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR)
            || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1;
    }
}
