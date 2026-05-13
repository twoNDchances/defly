<?php

namespace App\Models;

use App\Observers\UserObserver;
use App\Services\Identification;
use App\Traits\Models\Labellable;
use App\Traits\Models\Owner;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_verified', 'is_root', 'is_activated', 'created_by', 'verification_token'])]
#[Hidden(['password', 'remember_token', 'verification_token'])]
#[ObservedBy(UserObserver::class)]
class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasUuids, Labellable, Notifiable, Owner;

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

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'defly-manager'
            && $this->is_verified
            && $this->is_activated;
    }

    #[Scope]
    protected function excludeCurrent(Builder $query): void
    {
        $query->whereNot('id', Identification::getId());
    }

    #[Scope]
    protected function excludeRoot(Builder $query): void
    {
        if (Identification::isRoot()) {
            return;
        }
        $query->where('is_root', false);
    }

    public function getUsers()
    {
        return $this->hasMany(User::class, $this->getCreatedByField());
    }

    public function getGroups()
    {
        return $this->hasMany(Group::class, $this->getCreatedByField());
    }

    public function getPermissions()
    {
        return $this->hasMany(Permission::class, $this->getCreatedByField());
    }

    public function getLabels()
    {
        return $this->hasMany(Label::class, $this->getCreatedByField());
    }

    public function getWordlists()
    {
        return $this->hasMany(Wordlist::class, $this->getCreatedByField());
    }

    public function getEngines()
    {
        return $this->hasMany(Engine::class, $this->getCreatedByField());
    }

    public function getTargets()
    {
        return $this->hasMany(Target::class, $this->getCreatedByField());
    }

    public function getActions()
    {
        return $this->hasMany(Action::class, $this->getCreatedByField());
    }

    public function getRules()
    {
        return $this->hasMany(Rule::class, $this->getCreatedByField());
    }

    public function getPrinciples()
    {
        return $this->hasMany(Principle::class, $this->getCreatedByField());
    }

    public function getDecisions()
    {
        return $this->hasMany(Decision::class, $this->getCreatedByField());
    }

    public function getDefenders()
    {
        return $this->hasMany(Defender::class, $this->getCreatedByField());
    }

    public function getKeys()
    {
        return $this->hasMany(Key::class, $this->getCreatedByField());
    }

    public function getTimelines()
    {
        return $this->hasMany(Timeline::class, $this->getCreatedByField());
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'users_groups', 'user', 'group');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'users_permissions', 'user', 'permission');
    }
}
