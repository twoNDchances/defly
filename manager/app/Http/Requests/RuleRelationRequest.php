<?php

namespace App\Http\Requests;

class RuleRelationRequest extends RelationRequest
{
    protected function routeKey(): string
    {
        return 'rule';
    }
}
