<?php

namespace Tests\Feature\Services;

use App\Models\Label;
use App\Models\Permission;
use App\Models\User;
use App\Services\AssistantResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FilamentLivewireTestHelpers;
use Tests\TestCase;

class AssistantResourceServiceTest extends TestCase
{
    use FilamentLivewireTestHelpers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpFilamentLivewire();
    }

    public function test_resources_require_list_and_record_view_permissions(): void
    {
        $label = Label::query()->create([
            'name' => 'Restricted resource',
            'color' => '#ef4444',
        ]);
        $viewAny = Permission::query()->create([
            'name' => 'Label:List',
            'applied_for' => 'Label',
            'action' => 'viewAny',
        ]);
        $view = Permission::query()->create([
            'name' => 'Label:View',
            'applied_for' => 'Label',
            'action' => 'view',
        ]);
        /** @var User $user */
        $user = User::factory()->create([
            'is_verified' => true,
            'is_activated' => true,
            'is_root' => false,
        ]);

        $this->actingAs($user);

        $this->assertArrayNotHasKey('label', AssistantResource::typeOptions());
        $this->assertSame([], AssistantResource::options('label'));
        $this->assertNull(AssistantResource::reference('label', $label->id));

        $user->permissions()->attach($viewAny);

        $this->assertArrayHasKey('label', AssistantResource::typeOptions());
        $this->assertSame([], AssistantResource::options('label'));
        $this->assertNull(AssistantResource::reference('label', $label->id));

        $user->permissions()->attach($view);

        $this->assertSame(
            'Restricted resource',
            AssistantResource::options('label')[$label->id],
        );
        $reference = AssistantResource::reference('label', $label->id);
        $this->assertNotNull($reference);
        $this->assertSame($label->id, $reference['id']);

        $user->permissions()->detach($view);

        $this->assertSame([], AssistantResource::snapshots([$reference]));
    }
}
