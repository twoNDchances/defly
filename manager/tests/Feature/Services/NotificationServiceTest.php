<?php

namespace Tests\Feature\Services;

use App\Filament\Resources\Labels\LabelResource;
use App\Models\Label;
use App\Models\User;
use App\Services\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_resolve_recipients_statuses_and_resource_urls(): void
    {
        $owner = User::factory()->create([
            'email' => 'notify-owner@example.com',
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $label = Label::query()->create([
            'name' => 'notify-label-'.Str::lower(Str::random(6)),
            'color' => '#44aaee',
            'created_by' => $owner->id,
        ]);
        $label->forceFill(['created_by' => $owner->id])->saveQuietly();

        Notification::sendToRequester(null, 'No recipient');
        Notification::sendToRequester($owner->email, 'Danger title', 'Danger body', Notification::STATUS_DANGER);
        Notification::sendToRequester($owner->email, 'Success title', 'Success body', Notification::STATUS_SUCCESS);
        Notification::sendToRequester($owner->email, 'Warning title', 'Warning body', Notification::STATUS_WARNING);
        Notification::sendForRecord(null, $label, 'Info title', 'Info body', Notification::STATUS_INFO, '/labels/'.$label->id, 'Open label');

        $this->assertDatabaseCount('notifications', 4);
        $this->assertNull(Notification::resourceUrl(self::class, $label));
        $this->assertIsString(Notification::resourceUrl(LabelResource::class, $label));
    }
}
