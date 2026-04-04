<?php

namespace App\Models;

use App\Observers\UserObserver;
use App\Traits\Models\Owner;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_verified', 'is_root', 'is_activated', 'created_by', 'verification_token'])]
#[Hidden(['password', 'remember_token', 'verification_token'])]
#[ObservedBy(UserObserver::class)]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasUuids;
    use Notifiable;
    use Owner;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'email' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'is_root' => 'boolean',
            'is_activated' => 'boolean',
            'verification_token' => 'string',
            'created_by' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function getUsers()
    {
        return $this->hasMany(User::class, $this->getCreatedByField());
    }

    public function getPermissions()
    {
        return $this->hasMany(Permission::class, $this->getCreatedByField());
    }

    public function getPolicies()
    {
        return $this->hasMany(Policy::class, $this->getCreatedByField());
    }

    public function policies()
    {
        return $this->belongsToMany(Policy::class, 'users_policies', 'user', 'policy');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'users_permissions', 'user', 'permission');
    }
}
