<?php

namespace Tests\Feature\Api;

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
}
