<?php

namespace Tests\Feature\Api;

class PermissionControllerTest extends ApiTestCase
{
    public function test_permissions_payload_endpoint_is_accessible(): void
    {
        $this->apiJson('GET', $this->apiRoute('permissions', 'payload'))->assertOk();
    }

    public function test_permissions_api_crud_validation_and_put_patch_behavior(): void
    {
        $this->apiJson('GET', $this->apiRoute('permissions', 'index'))->assertOk();
        $this->apiJson('POST', $this->apiRoute('permissions', 'store'), [], [])->assertUnprocessable();

        $this->apiJson('POST', $this->apiRoute('permissions', 'store'), [], [
            'name' => 'Invalid Permission',
            'applied_for' => 'User',
            'action' => 'not-an-action',
        ])->assertUnprocessable()->assertJsonValidationErrors(['action']);

        $storeResponse = $this->apiJson('POST', $this->apiRoute('permissions', 'store'), [], [
            'name' => 'User:List',
            'applied_for' => 'User',
            'action' => 'viewAny',
            'description' => 'List users',
        ])->assertCreated();

        $permissionId = (string) $storeResponse->json('id');

        $this->apiJson('GET', $this->apiRoute('permissions', 'show'), ['permission' => $permissionId])
            ->assertOk()
            ->assertJsonPath('id', $permissionId);

        $this->apiJson('PATCH', $this->apiRoute('permissions', 'update'), ['permission' => $permissionId], [
            'description' => 'Patched permission',
        ])->assertOk()->assertJsonPath('description', 'Patched permission');

        $this->apiJson('PUT', $this->apiRoute('permissions', 'update'), ['permission' => $permissionId], [
            'description' => 'Only description',
        ])->assertUnprocessable();

        $this->apiJson('PUT', $this->apiRoute('permissions', 'update'), ['permission' => $permissionId], [
            'name' => 'User:View',
            'applied_for' => 'User',
            'action' => 'view',
            'description' => 'View users',
        ])->assertOk()->assertJsonPath('name', 'User:View');

        $this->apiJson('DELETE', $this->apiRoute('permissions', 'destroy'), ['permission' => $permissionId])->assertNoContent();
        $this->assertDatabaseMissing('permissions', ['id' => $permissionId]);
    }
}
