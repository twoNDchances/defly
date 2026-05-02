<?php

namespace App\Models;

use App\Observers\KeyObserver;
use App\Services\Identification;
use App\Traits\Models\Owner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'token', 'expired_at', 'is_reused', 'description', 'created_by'])]
#[Hidden(['token'])]
#[ObservedBy(KeyObserver::class)]
class Key extends Model
{
    use HasUuids, Owner;

    protected function casts()
    {
        return [
            'name' => 'string',
            'token' => 'hashed',
            'expired_at' => 'datetime',
            'is_reused' => 'boolean',
            'description' => 'string',
            'created_by' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    #[Scope]
    protected function onlyOwner(Builder $query): void
    {
        $query->where('created_by', Identification::getId());
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'keys_groups', 'key', 'group');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'keys_permissions', 'key', 'permission');
    }
}
