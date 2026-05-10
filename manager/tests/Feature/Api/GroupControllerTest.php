<?php

namespace Tests\Feature\Api;

class GroupControllerTest extends ApiTestCase
{
    public function test_groups_payload_endpoint_is_accessible(): void
    {
        $this->apiJson('GET', $this->apiRoute('groups', 'payload'))->assertOk();
    }

    public function test_groups_api_crud_validation_and_put_patch_behavior(): void
    {
        $this->apiJson('GET', $this->apiRoute('groups', 'index'))->assertOk();
        $this->apiJson('POST', $this->apiRoute('groups', 'store'), [], [])->assertUnprocessable();

        $storeResponse = $this->apiJson('POST', $this->apiRoute('groups', 'store'), [], [
            'name' => 'security-group',
            'description' => 'Security team',
        ])->assertCreated();

        $groupId = (string) $storeResponse->json('id');

        $this->apiJson('GET', $this->apiRoute('groups', 'show'), ['group' => $groupId])
            ->assertOk()
            ->assertJsonPath('id', $groupId);

        $this->apiJson('PATCH', $this->apiRoute('groups', 'update'), ['group' => $groupId], [
            'description' => 'Patched group',
        ])->assertOk()->assertJsonPath('description', 'Patched group');

        $this->apiJson('PUT', $this->apiRoute('groups', 'update'), ['group' => $groupId], [
            'description' => 'Only description',
        ])->assertUnprocessable();

        $this->apiJson('PUT', $this->apiRoute('groups', 'update'), ['group' => $groupId], [
            'name' => 'security-group-replaced',
            'description' => 'Replaced group',
        ])->assertOk()->assertJsonPath('name', 'security-group-replaced');

        $this->apiJson('DELETE', $this->apiRoute('groups', 'destroy'), ['group' => $groupId])->assertNoContent();
        $this->assertDatabaseMissing('groups', ['id' => $groupId]);
    }
}
