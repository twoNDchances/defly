<?php

namespace Tests\Feature\Api;

class DefenderControllerTest extends ApiTestCase
{
    public function test_defenders_payload_endpoint_is_accessible(): void
    {
        $this->apiJson('GET', $this->apiRoute('defenders', 'payload'))->assertOk();
    }

    public function test_defenders_api_crud_validation_and_put_patch_behavior(): void
    {
        $this->apiJson('GET', $this->apiRoute('defenders', 'index'))->assertOk();
        $this->apiJson('POST', $this->apiRoute('defenders', 'store'), [], [])->assertUnprocessable();

        $storePayload = [
            'name' => 'primary-defender',
            'proxy_port' => 9948,
            'environment_variables' => [
                'PROXY_BACKEND_URL' => 'http://localhost',
                'PROXY_PORT' => '9948',
            ],
            'description' => 'Defender record.',
        ];

        $storeResponse = $this->apiJson('POST', $this->apiRoute('defenders', 'store'), [], $storePayload)
            ->assertCreated();

        $defenderId = (string) $storeResponse->json('id');

        $this->apiJson('GET', $this->apiRoute('defenders', 'show'), ['defender' => $defenderId])
            ->assertOk()
            ->assertJsonPath('id', $defenderId);

        $this->apiJson('PATCH', $this->apiRoute('defenders', 'update'), ['defender' => $defenderId], [
            'description' => 'Patched defender.',
        ])->assertOk()->assertJsonPath('description', 'Patched defender.');

        $this->apiJson('PUT', $this->apiRoute('defenders', 'update'), ['defender' => $defenderId], [
            'description' => 'Only description',
        ])->assertUnprocessable();

        $putPayload = [
            'name' => 'secondary-defender',
            'proxy_port' => 9950,
            'environment_variables' => [
                'PROXY_BACKEND_URL' => 'https://example.com',
                'PROXY_PORT' => '9950',
            ],
            'description' => 'Replaced defender.',
        ];

        $this->apiJson('PUT', $this->apiRoute('defenders', 'update'), ['defender' => $defenderId], $putPayload)
            ->assertOk()
            ->assertJsonPath('name', 'secondary-defender');

        $this->apiJson('DELETE', $this->apiRoute('defenders', 'destroy'), ['defender' => $defenderId])->assertNoContent();
        $this->assertDatabaseMissing('defenders', ['id' => $defenderId]);
    }
}
