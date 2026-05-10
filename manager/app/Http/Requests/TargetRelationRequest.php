<?php

namespace App\Http\Requests;

class TargetRelationRequest extends RelationRequest
{
    protected function routeKey(): string
    {
        return 'target';
    }
}
