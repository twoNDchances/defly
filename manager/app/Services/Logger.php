<?php

namespace App\Services;

use App\Models\Timeline;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class Logger
{
    public static function created(Model $resource): ?Timeline
    {
        return static::log($resource, 'create');
    }

    public static function updated(Model $resource): ?Timeline
    {
        if ($resource->wasRecentlyCreated) {
            return null;
        }

        return static::log($resource, 'update');
    }

    public static function deleted(Model $resource): ?Timeline
    {
        return static::log($resource, 'delete');
    }

    public static function log(Model $resource, string $action): ?Timeline
    {
        if (! static::shouldLog($resource, $action)) {
            return null;
        }

        try {
            return Timeline::withoutEvents(fn () => Timeline::query()->create([
                'created_by' => Identification::getId(),
                'ipv4' => static::ipv4(),
                'ipv6' => static::ipv6(),
                'method' => static::method(),
                'path' => static::path(),
                'action' => $action,
                'resource_type' => $resource->getMorphClass(),
                'resource_id' => (string) $resource->getKey(),
            ]));
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    public static function logMany(iterable $resources, string $action): void
    {
        foreach ($resources as $resource) {
            if ($resource instanceof Model) {
                static::log($resource, $action);
            }
        }
    }

    protected static function shouldLog(Model $resource, string $action): bool
    {
        return filled($action)
            && ($resource->exists || $action === 'delete')
            && filled($resource->getKey())
            && ! ($resource instanceof Timeline)
            && ! app()->runningInConsole();
    }

    protected static function method(): ?string
    {
        $method = request()?->method();

        return filled($method) ? strtolower($method) : null;
    }

    protected static function path(): ?string
    {
        $path = request()?->path();

        return filled($path) ? $path : null;
    }

    protected static function ipv4(): ?string
    {
        return static::ip(FILTER_FLAG_IPV4);
    }

    protected static function ipv6(): ?string
    {
        return static::ip(FILTER_FLAG_IPV6);
    }

    protected static function ip(int $flag): ?string
    {
        $ip = request()?->ip();

        if (! is_string($ip) || blank($ip)) {
            return null;
        }

        return filter_var($ip, FILTER_VALIDATE_IP, $flag) ? $ip : null;
    }
}
