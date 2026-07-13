<?php

namespace App\Http\Requests;

class GuardRelationRequest extends RelationRequest
{
    protected function routeKey(): string
    {
        return 'guard';
    }
}
