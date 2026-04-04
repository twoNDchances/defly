<?php

namespace App\Models;

use App\Observers\PermissionObserver;
use App\Traits\Models\Labellable;
use App\Traits\Models\Owner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'description', 'applied_for', 'action', 'created_by'])]
#[ObservedBy(PermissionObserver::class)]
class Permission extends Model
{
    use HasUuids, Labellable, Owner;

    public function casts()
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'description' => 'string',
            'applied_for' => 'string',
            'action' => 'string',
            'created_by' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_permissions', 'permission', 'user');
    }

    public function policies()
    {
        return $this->belongsToMany(Policy::class, 'policies_permissions', 'permission', 'policy');
    }
}
