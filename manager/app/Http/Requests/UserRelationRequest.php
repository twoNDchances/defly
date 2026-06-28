<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserRelationRequest extends RelationRequest
{
    protected function routeKey(): string
    {
        return 'user';
    }

    protected function canViewOwnerRecord(Model $record): bool
    {
        return $record instanceof User && $this->canAccessUserRecord($record, 'view');
    }

    protected function canUpdateOwnerRecord(Model $record): bool
    {
        return $record instanceof User && $this->canAccessUserRecord($record, 'update');
    }
}
