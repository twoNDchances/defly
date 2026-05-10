<?php

namespace App\Http\Requests;

class DecisionRelationRequest extends RelationRequest
{
    protected function routeKey(): string
    {
        return 'decision';
    }
}
