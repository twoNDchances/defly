<?php

namespace App\Http\Requests;

class EngineRelationRequest extends RelationRequest
{
    protected function routeKey(): string
    {
        return 'engine';
    }
}
