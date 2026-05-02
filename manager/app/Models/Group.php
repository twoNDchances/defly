<?php

namespace App\Models;

use App\Observers\GroupObserver;
use App\Traits\Models\Labellable;
use App\Traits\Models\Owner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'description', 'created_by'])]
#[ObservedBy(GroupObserver::class)]
class Group extends Model
{
    use HasUuids, Labellable, Owner;

    protected function casts()
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'description' => 'string',
            'created_by' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_groups', 'group', 'user');
    }

    public function keys()
    {
        return $this->belongsToMany(Key::class, 'keys_groups', 'group', 'key');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'groups_permissions', 'group', 'permission');
    }
}
