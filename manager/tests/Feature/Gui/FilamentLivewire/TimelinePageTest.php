<?php

namespace Tests\Feature\Gui\FilamentLivewire;

use App\Filament\Components\Timeline\TimelineTable;
use App\Filament\Resources\Timelines\Pages\ListTimelines;
use App\Models\Conservation;
use App\Models\Label;
use App\Models\Permission;
use App\Models\Timeline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FilamentLivewireTestHelpers;
use Tests\TestCase;

class TimelinePageTest extends TestCase
{
    use FilamentLivewireTestHelpers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpFilamentLivewire();
    }

    public function test_timeline_resource_type_uses_localized_conversation_label(): void
    {
        $locale = app()->getLocale();
        app()->setLocale('vi');

        try {
            $options = TimelineTable::resourceTypeOptionsAndColors()['options'];

            $this->assertSame('Hội thoại', $options[Conservation::class]);
        } finally {
            app()->setLocale($locale);
        }
    }

    public function test_current_user_can_bulk_delete_selected_timelines(): void
    {
        $firstTimeline = $this->createTimelineFor($this->root);
        $secondTimeline = $this->createTimelineFor($this->root);
        $otherUserTimeline = $this->createTimelineFor(User::factory()->create());

        $this->livewirePage(ListTimelines::class)
            ->assertTableBulkActionExists('delete')
            ->callTableBulkAction('delete', [$firstTimeline, $secondTimeline]);

        $this->assertDatabaseMissing('timelines', ['id' => $firstTimeline->id]);
        $this->assertDatabaseMissing('timelines', ['id' => $secondTimeline->id]);
        $this->assertDatabaseHas('timelines', ['id' => $otherUserTimeline->id]);
    }

    public function test_timeline_table_shows_only_the_current_users_records(): void
    {
        $currentUserTimeline = $this->createTimelineFor($this->root);
        $otherUserTimeline = $this->createTimelineFor(User::factory()->create());

        $component = $this->livewirePage(ListTimelines::class)
            ->assertSet('activeTab', 'mine')
            ->assertSee(__('tables.timeline.tabs.mine'))
            ->assertSee(__('tables.timeline.tabs.all'))
            ->assertCountTableRecords(1);

        $recordIds = collect($component->instance()->getTableRecords()->items())
            ->pluck('id');

        $this->assertTrue($recordIds->contains($currentUserTimeline->id));
        $this->assertFalse($recordIds->contains($otherUserTimeline->id));

        $component->set('activeTab', 'all')
            ->assertTableBulkActionVisible('delete')
            ->assertCountTableRecords(2);

        $recordIds = collect($component->instance()->getTableRecords()->items())
            ->pluck('id');

        $this->assertTrue($recordIds->contains($currentUserTimeline->id));
        $this->assertTrue($recordIds->contains($otherUserTimeline->id));
    }

    public function test_all_timelines_tab_is_hidden_and_inaccessible_to_non_root_users(): void
    {
        $user = User::factory()->create([
            'is_root' => false,
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $permission = Permission::withoutEvents(fn (): Permission => Permission::query()->create([
            'name' => 'Timeline:List',
            'applied_for' => 'Timeline',
            'action' => 'viewAny',
        ]));
        $deletePermission = Permission::withoutEvents(fn (): Permission => Permission::query()->create([
            'name' => 'Timeline:Multi-delete',
            'applied_for' => 'Timeline',
            'action' => 'deleteAny',
        ]));
        $user->permissions()->attach([$permission->id, $deletePermission->id]);

        $currentUserTimeline = $this->createTimelineFor($user);
        $otherUserTimeline = $this->createTimelineFor($this->root);

        $this->actingAs($user);

        $component = $this->livewirePage(ListTimelines::class)
            ->assertSet('activeTab', null)
            ->assertDontSee(__('tables.timeline.tabs.mine'))
            ->assertDontSee(__('tables.timeline.tabs.all'))
            ->assertTableBulkActionVisible('delete')
            ->assertCountTableRecords(1);

        $component->set('activeTab', 'all')
            ->assertTableBulkActionHidden('delete')
            ->assertCountTableRecords(1);

        $recordIds = collect($component->instance()->getTableRecords()->items())
            ->pluck('id');

        $this->assertTrue($recordIds->contains($currentUserTimeline->id));
        $this->assertFalse($recordIds->contains($otherUserTimeline->id));

        foreach (['created_by', 'resource_type', 'action', 'method', 'created_at'] as $filter) {
            $component->assertTableFilterHidden($filter);
        }
    }

    public function test_root_can_filter_all_timelines_by_user_resource_action_method_and_period(): void
    {
        $otherUser = User::factory()->create();
        $this->createTimelineFor($this->root, [
            'action' => 'create',
            'method' => 'get',
            'resource_type' => User::class,
            'created_at' => now(),
        ]);
        $otherUserTimeline = $this->createTimelineFor($otherUser, [
            'action' => 'delete',
            'method' => 'post',
            'resource_type' => Label::class,
            'created_at' => now()->subWeek(),
        ]);

        $component = $this->livewirePage(ListTimelines::class)
            ->set('activeTab', 'all')
            ->assertCountTableRecords(2);

        foreach (['created_by', 'resource_type', 'action', 'method', 'created_at'] as $filter) {
            $component->assertTableFilterVisible($filter);
        }

        foreach ([
            ['created_by', $otherUser],
            ['resource_type', Label::class],
            ['action', 'delete'],
            ['method', 'post'],
            ['created_at', [
                'from' => now()->subDays(8)->toDateString(),
                'until' => now()->subDays(6)->toDateString(),
            ]],
        ] as [$filter, $value]) {
            $component
                ->filterTable($filter, $value)
                ->assertCountTableRecords(1);

            $recordIds = collect($component->instance()->getTableRecords()->items())
                ->pluck('id');

            $this->assertSame([$otherUserTimeline->id], $recordIds->all());

            $component->resetTableFilters();
        }
    }

    private function createTimelineFor(User $user, array $attributes = []): Timeline
    {
        return Timeline::withoutEvents(function () use ($attributes, $user): Timeline {
            $timeline = new Timeline;
            $timeline->forceFill(array_merge([
                'created_by' => $user->id,
                'ipv4' => '127.0.0.1',
                'method' => 'get',
                'path' => '/timeline-test',
                'action' => 'view',
                'resource_type' => User::class,
                'resource_id' => $user->id,
            ], $attributes));
            $timeline->save();

            return $timeline;
        });
    }
}
