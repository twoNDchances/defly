<?php

namespace Tests\Feature\Api;

use App\Models\Timeline;
use App\Models\User;

class TimelineControllerTest extends ApiTestCase
{
    public function test_timelines_api_list_get_delete_and_restricted_methods(): void
    {
        $timeline = Timeline::withoutEvents(fn () => Timeline::query()->create([
            'created_by' => $this->user->id,
            'ipv4' => '127.0.0.1',
            'method' => 'get',
            'path' => '/api/test',
            'action' => 'create',
            'resource_type' => User::class,
            'resource_id' => $this->user->id,
        ]));

        $this->apiJson('GET', $this->apiRoute('timelines', 'index'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $timeline->id);

        $this->apiJson('GET', $this->apiRoute('timelines', 'show'), ['timeline' => $timeline->id])
            ->assertOk()
            ->assertJsonPath('id', $timeline->id);

        $this->apiJsonToUrl(
            'PATCH',
            route($this->apiRoute('timelines', 'show'), ['timeline' => $timeline->id]),
            ['action' => 'update']
        )->assertMethodNotAllowed();

        $this->apiJson('DELETE', $this->apiRoute('timelines', 'destroy'), ['timeline' => $timeline->id])
            ->assertNoContent();

        $this->assertDatabaseMissing('timelines', ['id' => $timeline->id]);
    }
}
