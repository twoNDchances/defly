<?php

namespace Tests\Feature\Api;

use App\Models\Guard;
use App\Models\User;
use Tests\Support\ApiRelationTestHelpers;

class GuardControllerTest extends ApiTestCase
{
    use ApiRelationTestHelpers;

    public function test_guards_payload_endpoint_is_accessible(): void
    {
        $this->apiJson('GET', $this->apiRoute('guards', 'payload'))->assertOk();
    }

    public function test_guards_api_crud_validation_and_put_patch_behavior(): void
    {
        $this->apiJson('GET', $this->apiRoute('guards', 'index'))->assertOk();
        $this->apiJson('POST', $this->apiRoute('guards', 'store'), [], [])->assertUnprocessable();

        $this->apiJson('POST', $this->apiRoute('guards', 'store'), [], [
            'name' => 'Invalid Guard Name',
        ])->assertUnprocessable()->assertJsonValidationErrors(['name']);

        $storeResponse = $this->apiJson('POST', $this->apiRoute('guards', 'store'), [], [
            'name' => 'production-edge-operators',
            'description' => 'Production operators',
            'expired_at' => now()->addDay()->toISOString(),
        ])->assertCreated();

        $guardId = (string) $storeResponse->json('id');

        $this->apiJson('GET', $this->apiRoute('guards', 'show'), ['guard' => $guardId])
            ->assertOk()
            ->assertJsonPath('id', $guardId);

        $this->apiJson('PATCH', $this->apiRoute('guards', 'update'), ['guard' => $guardId], [
            'description' => 'Patched guard',
        ])->assertOk()->assertJsonPath('description', 'Patched guard');

        $this->apiJson('PUT', $this->apiRoute('guards', 'update'), ['guard' => $guardId], [
            'description' => 'Only description',
        ])->assertUnprocessable();

        $this->apiJson('PUT', $this->apiRoute('guards', 'update'), ['guard' => $guardId], [
            'name' => 'production-edge-operators-replaced',
            'description' => 'Replaced guard',
            'expired_at' => null,
        ])->assertOk()->assertJsonPath('name', 'production-edge-operators-replaced');

        $this->apiJson('DELETE', $this->apiRoute('guards', 'destroy'), ['guard' => $guardId])->assertNoContent();
        $this->assertDatabaseMissing('guards', ['id' => $guardId]);
    }

    public function test_guard_relation_endpoints_attach_list_and_detach_related_records(): void
    {
        $guard = Guard::query()->create([
            'name' => 'api-guard-relations',
            'description' => 'Guard relations',
        ]);
        $user = User::factory()->create([
            'is_root' => false,
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $defender = $this->apiDefender('guard-relation');
        $label = $this->label('guard-relation');

        $this->attachListDetach('guards.users', ['guard' => $guard->id], $user->id);

        $guard->users()->syncWithoutDetaching($this->user->id);
        $this->attachListDetach('guards.defenders', ['guard' => $guard->id], $defender->id);

        $this->attachListDetach('guards.labels', ['guard' => $guard->id], $label->id);
    }
}
