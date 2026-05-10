<?php

namespace App\Http\Requests;

class ActionRelationRequest extends RelationRequest
{
    protected function routeKey(): string
    {
        return 'action';
    }
}
