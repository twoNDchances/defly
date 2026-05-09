<?php

namespace App\Services;

class ApiPayload
{
    public static function resource(string $resource, array $operations): array
    {
        $payload = [
            'authentication' => ApiAuthentication::details(),
            'headers' => ApiAuthentication::headers(),
        ];

        foreach ($operations as $name => $operation) {
            $payload[$name] = [
                'method' => strtoupper($operation['method']),
                'url' => self::url($resource, $operation['path'] ?? null),
                'body' => ApiAuthentication::body($operation['body'] ?? []),
            ];
        }

        return $payload;
    }

    private static function url(string $resource, ?string $path = null): string
    {
        $segments = array_filter([
            'api',
            config('customization.backend.urls.api_prefix'),
            trim($resource, '/'),
            $path ? trim($path, '/') : null,
        ]);

        return url(implode('/', $segments));
    }
}
