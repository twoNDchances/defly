<?php

namespace Tests\Support;

use App\Services\Connector;

class ConnectorHarness extends Connector
{
    protected static array $headers = [];

    public static function configure(
        ?string $baseUrl,
        ?string $pathPrefix,
        ?string $username,
        ?string $password,
        array $headers = [],
    ) {
        static::$baseUrl = $baseUrl;
        static::$pathPrefix = $pathPrefix;
        static::$username = $username;
        static::$password = $password;
        static::$headers = $headers;
    }

    public static function baseUriPublic(): string
    {
        return static::baseUri();
    }

    protected static function requestHeaders(): array
    {
        return static::$headers;
    }
}
