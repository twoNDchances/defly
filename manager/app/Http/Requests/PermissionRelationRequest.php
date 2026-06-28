<?php

namespace App\Http\Requests;

class PermissionRelationRequest extends RelationRequest
{
    protected function routeKey(): string
    {
        return 'permission';
    }
}
