<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Tests\Support\ApiRelationTestHelpers;

class UserAccessRelationEndpointsTest extends ApiTestCase
{
    use ApiRelationTestHelpers;

    public function test_user_group_permission_and_label_relations_can_be_attached_listed_and_detached(): void
    {
        $member = User::factory()->create([
            'email' => 'member@example.com',
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $group = $this->group('operators');
        $permission = $this->permission('Action:List', 'Action', 'viewAny');
        $label = $this->label('people');

        $this->attachListDetach('users.groups', ['user' => $member->id], $group->id);
        $this->attachListDetach('users.permissions', ['user' => $member->id], $permission->id);
        $this->attachListDetach('users.labels', ['user' => $member->id], $label->id);

        $this->attachListDetach('groups.users', ['group' => $group->id], $member->id);
        $this->attachListDetach('groups.permissions', ['group' => $group->id], $permission->id);
        $this->attachListDetach('groups.labels', ['group' => $group->id], $label->id);

        $this->attachListDetach('permissions.users', ['permission' => $permission->id], $member->id);
        $this->attachListDetach('permissions.groups', ['permission' => $permission->id], $group->id);
        $this->attachListDetach('permissions.labels', ['permission' => $permission->id], $label->id);

        $this->attachListDetach('labels.users', ['label' => $label->id], $member->id);
        $this->attachListDetach('labels.groups', ['label' => $label->id], $group->id);
        $this->attachListDetach('labels.permissions', ['label' => $label->id], $permission->id);
    }
}
