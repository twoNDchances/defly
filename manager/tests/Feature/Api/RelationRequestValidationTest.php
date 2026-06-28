<?php

namespace Tests\Feature\Api;

use App\Models\Key;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\Support\ApiRelationTestHelpers;

class RelationRequestValidationTest extends ApiTestCase
{
    use ApiRelationTestHelpers;

    public function test_relation_requests_validate_ids(): void
    {
        $action = $this->action();

        $this->apiJson('POST', $this->apiRoute('actions.labels', 'attach'), ['action' => $action->id], [
            'ids' => [],
        ])->assertUnprocessable()->assertJsonValidationErrors(['ids']);

        $this->apiJson('POST', $this->apiRoute('actions.labels', 'attach'), ['action' => $action->id], [
            'ids' => ['missing-id'],
        ])->assertUnprocessable()->assertJsonValidationErrors(['ids.0']);
    }

    public function test_relation_requests_use_view_for_list_and_update_for_mutations(): void
    {
        $action = $this->action();
        $label = $this->label('relation-auth');

        $this->useNonRootApiUser([
            $this->permission('Action:View', 'Action', 'view')->id,
        ]);

        $this->apiJson('GET', $this->apiRoute('actions.labels', 'index'), ['action' => $action->id])
            ->assertOk();

        $this->apiJson('POST', $this->apiRoute('actions.labels', 'attach'), ['action' => $action->id], [
            'ids' => [$label->id],
        ])->assertForbidden();
    }

    public function test_relation_requests_require_view_permission_for_related_ids(): void
    {
        $action = $this->action();
        $label = $this->label('relation-related-auth');
        $actionView = $this->permission('Action:View Related', 'Action', 'view');
        $actionUpdate = $this->permission('Action:Update Related', 'Action', 'update');
        $labelView = $this->permission('Label:View Related', 'Label', 'view');
        $user = $this->useNonRootApiUser([
            $actionView->id,
            $actionUpdate->id,
        ]);

        $this->apiJson('POST', $this->apiRoute('actions.labels', 'attach'), ['action' => $action->id], [
            'ids' => [$label->id],
        ])->assertForbidden();

        $user->permissions()->attach($labelView->id);

        $this->apiJson('POST', $this->apiRoute('actions.labels', 'attach'), ['action' => $action->id], [
            'ids' => [$label->id],
        ])->assertOk()->assertJsonFragment(['id' => $label->id]);
    }

    /**
     * @param  array<int, string>  $permissionIds
     */
    private function useNonRootApiUser(array $permissionIds): User
    {
        $password = 'relation-user-pass';
        $token = 'relation-user-token-'.Str::lower(Str::random(6));
        $user = User::factory()->create([
            'email' => 'relation-user-'.Str::lower(Str::random(6)).'@example.com',
            'password' => $password,
            'is_root' => false,
            'is_verified' => true,
            'is_activated' => true,
        ]);

        Key::withoutEvents(fn () => Key::query()->create([
            'name' => 'relation-user-token-'.Str::lower(Str::random(6)),
            'token' => $token,
            'is_reused' => true,
            'created_by' => $user->id,
        ]));

        $user->permissions()->attach($permissionIds);

        $this->user = $user;
        $this->password = $password;
        $this->apiToken = $token;

        return $user;
    }
}
