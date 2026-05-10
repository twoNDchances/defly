<?php

namespace Tests\Feature\Api;

class LabelControllerTest extends ApiTestCase
{
    public function test_labels_payload_endpoint_is_accessible(): void
    {
        $this->apiJson('GET', $this->apiRoute('labels', 'payload'))->assertOk();
    }

    public function test_labels_api_crud_validation_and_put_patch_behavior(): void
    {
        $this->apiJson('GET', $this->apiRoute('labels', 'index'))->assertOk();
        $this->apiJson('POST', $this->apiRoute('labels', 'store'), [], [])->assertUnprocessable();

        $storeResponse = $this->apiJson('POST', $this->apiRoute('labels', 'store'), [], [
            'name' => 'critical-label',
            'color' => '#ff5500',
            'description' => 'Critical',
        ])->assertCreated();

        $labelId = (string) $storeResponse->json('id');

        $this->apiJson('GET', $this->apiRoute('labels', 'show'), ['label' => $labelId])
            ->assertOk()
            ->assertJsonPath('id', $labelId);

        $this->apiJson('PATCH', $this->apiRoute('labels', 'update'), ['label' => $labelId], [
            'description' => 'Patched label',
        ])->assertOk()->assertJsonPath('description', 'Patched label');

        $this->apiJson('PUT', $this->apiRoute('labels', 'update'), ['label' => $labelId], [
            'description' => 'Only description',
        ])->assertUnprocessable();

        $this->apiJson('PUT', $this->apiRoute('labels', 'update'), ['label' => $labelId], [
            'name' => 'critical-label-replaced',
            'color' => '#0ea5e9',
            'description' => 'Replaced label',
        ])->assertOk()->assertJsonPath('name', 'critical-label-replaced');

        $this->apiJson('DELETE', $this->apiRoute('labels', 'destroy'), ['label' => $labelId])->assertNoContent();
        $this->assertDatabaseMissing('labels', ['id' => $labelId]);
    }
}
