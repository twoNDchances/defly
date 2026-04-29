<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

abstract class Connector
{
    protected static ?string $baseUrl = null;

    protected static ?string $pathPrefix = null;

    protected static ?string $username = null;

    protected static ?string $password = null;

    protected static function request(array $headers = []): PendingRequest
    {
        $request = Http::baseUrl(static::baseUri())
            ->acceptJson()
            ->asJson();
        $requestHeaders = array_merge(static::requestHeaders(), $headers);
        if ($requestHeaders !== []) {
            $request = $request->withHeaders($requestHeaders);
        }
        $requestOptions = static::requestOptions();
        if ($requestOptions !== []) {
            $request = $request->withOptions($requestOptions);
        }

        if (filled(static::username()) || filled(static::password())) {
            $request->withBasicAuth(
                (string) static::username(),
                (string) static::password(),
            );
        }

        return $request;
    }

    public static function send(
        string $path,
        string $method,
        array $data = [],
        array $query = [],
        array $headers = [],
    ): Response {
        $path = static::normalizePath($path);
        $method = strtolower(trim($method));

        return match ($method) {
            'get' => static::request($headers)->get(
                $path,
                $query !== [] ? $query : $data
            ),
            'post' => static::request($headers)->post($path, $data),
            'put' => static::request($headers)->put($path, $data),
            'patch' => static::request($headers)->patch($path, $data),
            'delete' => static::request($headers)->delete($path, $data),
        };
    }

    protected static function baseUrl(): ?string
    {
        return static::$baseUrl;
    }

    protected static function pathPrefix(): ?string
    {
        return static::$pathPrefix;
    }

    protected static function username(): ?string
    {
        return static::$username;
    }

    protected static function password(): ?string
    {
        return static::$password;
    }

    protected static function requestOptions(): array
    {
        return [];
    }

    protected static function requestHeaders(): array
    {
        return [];
    }

    protected static function normalizePath(string $path): string
    {
        return trim($path, " \t\n\r\0\x0B/");
    }

    protected static function baseUri(): string
    {
        $baseUrl = rtrim((string) static::baseUrl(), '/');
        $pathPrefix = static::normalizePath((string) static::pathPrefix());

        return $pathPrefix === '' ? $baseUrl : "{$baseUrl}/{$pathPrefix}";
    }
}
