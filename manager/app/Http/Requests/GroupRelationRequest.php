<?php

namespace App\Http\Requests;

class GroupRelationRequest extends RelationRequest
{
    protected function routeKey(): string
    {
        return 'group';
    }
}
