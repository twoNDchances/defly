<?php

namespace Tests\Feature\Api;

use App\Enums\Phase;

class PrincipleControllerTest extends ApiTestCase
{
    public function test_principles_payload_endpoint_is_accessible(): void
    {
        $this->apiJson('GET', $this->apiRoute('principles', 'payload'))->assertOk();
    }

    public function test_principles_api_crud_validation_and_put_patch_behavior(): void
    {
        $this->apiJson('GET', $this->apiRoute('principles', 'index'))->assertOk();
        $this->apiJson('POST', $this->apiRoute('principles', 'store'), [], [])->assertUnprocessable();

        $storeResponse = $this->apiJson('POST', $this->apiRoute('principles', 'store'), [], [
            'name' => 'request-principle',
            'level' => 1,
            'phase' => Phase::One->value,
            'description' => 'Principle',
        ])->assertCreated();

        $principleId = (string) $storeResponse->json('id');

        $this->apiJson('GET', $this->apiRoute('principles', 'show'), ['principle' => $principleId])
            ->assertOk()
            ->assertJsonPath('id', $principleId);

        $this->apiJson('PATCH', $this->apiRoute('principles', 'update'), ['principle' => $principleId], [
            'description' => 'Patched principle',
        ])->assertOk()->assertJsonPath('description', 'Patched principle');

        $this->apiJson('PUT', $this->apiRoute('principles', 'update'), ['principle' => $principleId], [
            'description' => 'Only description',
        ])->assertUnprocessable();

        $this->apiJson('PUT', $this->apiRoute('principles', 'update'), ['principle' => $principleId], [
            'name' => 'response-principle',
            'level' => 2,
            'phase' => Phase::Two->value,
            'description' => 'Replaced principle',
        ])->assertOk()->assertJsonPath('name', 'response-principle');

        $this->apiJson('DELETE', $this->apiRoute('principles', 'destroy'), ['principle' => $principleId])->assertNoContent();
        $this->assertDatabaseMissing('principles', ['id' => $principleId]);
    }
}
