<?php

namespace App\Http\Requests;

class LabelRelationRequest extends RelationRequest
{
    protected function routeKey(): string
    {
        return 'label';
    }
}
