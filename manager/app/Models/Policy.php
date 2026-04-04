<?php

namespace App\Models;

use App\Observers\PolicyObserver;
use App\Traits\Models\Owner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'description', 'created_by'])]
#[ObservedBy(PolicyObserver::class)]
class Policy extends Model
{
    use HasUuids;
    use Owner;

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
        return $this->belongsToMany(User::class, 'users_policies', 'policy', 'user');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'policies_permissions', 'policy', 'permission');
    }
}
