<?php

namespace App\Http\Requests;

class WordlistRelationRequest extends RelationRequest
{
    protected function routeKey(): string
    {
        return 'wordlist';
    }
}
