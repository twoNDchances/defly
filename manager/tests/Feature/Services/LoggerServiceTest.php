<?php

namespace Tests\Feature\Services;

use App\Models\Label;
use App\Models\Timeline;
use App\Models\User;
use App\Services\Logger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use ReflectionProperty;
use Tests\TestCase;

class LoggerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_logger_writes_timeline_entries_when_not_running_in_console(): void
    {
        $consoleFlag = new ReflectionProperty($this->app, 'isRunningInConsole');
        $consoleFlag->setAccessible(true);
        $previous = $consoleFlag->getValue($this->app);
        $consoleFlag->setValue($this->app, false);

        try {
            $user = User::factory()->create([
                'is_root' => true,
                'is_verified' => true,
                'is_activated' => true,
            ]);
            $this->actingAs($user);
            $this->app->instance('request', Request::create('/audit/path', 'POST', server: ['REMOTE_ADDR' => '127.0.0.1']));

            $label = Label::query()->create([
                'name' => 'logger-label-'.Str::lower(Str::random(6)),
                'color' => '#dd4477',
                'created_by' => $user->id,
            ]);

            $created = Logger::created($label);
            $this->assertInstanceOf(Timeline::class, $created);
            $this->assertSame('create', $created->action);
            $this->assertSame('post', $created->method);
            $this->assertSame('audit/path', $created->path);
            $this->assertSame('127.0.0.1', $created->ipv4);

            $this->assertNull(Logger::updated($label));
            $label->wasRecentlyCreated = false;
            $this->assertInstanceOf(Timeline::class, Logger::updated($label));
            $this->assertInstanceOf(Timeline::class, Logger::deleted($label));
            $this->assertNull(Logger::log($label, ''));
            $this->assertNull(Logger::log(new Timeline(), 'view'));

            Logger::logMany([$label, 'not-a-model'], 'bulk');

            $this->app->instance('request', Request::create('/ipv6', 'GET', server: ['REMOTE_ADDR' => '2001:db8::1']));
            $ipv6Timeline = Logger::log($label, 'ipv6');
            $this->assertSame('2001:db8::1', $ipv6Timeline?->ipv6);
        } finally {
            $consoleFlag->setValue($this->app, $previous);
        }
    }
}
