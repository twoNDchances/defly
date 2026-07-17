<?php

namespace App\Services;

class DefenderEnvironment
{
    public static function mergeDatabaseConnection(array $environmentVariables): array
    {
        return array_replace(
            self::normalize($environmentVariables),
            self::databaseConnection(),
        );
    }

    public static function databaseConnection(): array
    {
        $database = config('database.connections.mysql', []);

        return [
            'DATABASE_HOST' => self::stringValue($database['host'] ?? null, 'mariadb'),
            'DATABASE_PORT' => self::stringValue($database['port'] ?? null, '3306'),
            'DATABASE_NAME' => self::stringValue($database['database'] ?? null, 'defly_manager'),
            'DATABASE_USER' => self::stringValue($database['username'] ?? null, 'defly'),
            'DATABASE_PASS' => self::stringValue($database['password'] ?? null),
        ];
    }

    private static function normalize(array $variables): array
    {
        $normalized = [];

        foreach ($variables as $key => $item) {
            if (is_string($key) && (! is_array($item))) {
                $normalized[$key] = $item;

                continue;
            }

            if (is_array($item) && array_key_exists('key', $item)) {
                $normalized[(string) $item['key']] = $item['value'] ?? null;
            }
        }

        return $normalized;
    }

    private static function stringValue(mixed $value, string $fallback = ''): string
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        if ($value === null) {
            return $fallback;
        }

        $value = (string) $value;

        return $value === '' ? $fallback : $value;
    }
}
