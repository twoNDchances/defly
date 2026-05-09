<?php

namespace App\Services;

use Illuminate\Http\Request;

class ApiAuthentication
{
    public const TOKEN_PLACEHOLDER = '<api-token>';

    public static function tokenLocation(): string
    {
        return config('customization.backend.apis.authentication.token_location', 'header');
    }

    public static function tokenKeyName(): string
    {
        return config('customization.backend.apis.authentication.token_key_name', 'X-Token-Key');
    }

    public static function tokenFrom(Request $request): mixed
    {
        return match (self::tokenLocation()) {
            'body' => $request->input(self::tokenKeyName()),
            default => $request->headers->get(self::tokenKeyName()),
        };
    }

    public static function headers(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Accept-Language' => '<vi|en>',
            'Content-Type' => 'application/json',
        ];

        if (self::tokenLocation() === 'header') {
            $headers[self::tokenKeyName()] = self::TOKEN_PLACEHOLDER;
        }

        return $headers;
    }

    public static function body(array $body): array
    {
        if (self::tokenLocation() !== 'body') {
            return $body;
        }

        return [
            self::tokenKeyName() => self::TOKEN_PLACEHOLDER,
            ...$body,
        ];
    }

    public static function details(): array
    {
        return [
            'basic' => [
                'username' => '<email>',
                'password' => '<password>',
            ],
            'token' => [
                'location' => self::tokenLocation(),
                'key' => self::tokenKeyName(),
                'value' => self::TOKEN_PLACEHOLDER,
            ],
        ];
    }
}
