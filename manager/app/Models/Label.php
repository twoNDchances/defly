<?php

namespace App\Models;

use App\Observers\LabelObserver;
use App\Traits\Models\Owner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'color', 'description', 'created_by'])]
#[ObservedBy(LabelObserver::class)]
class Label extends Model
{
    use HasUuids, Owner;

    public function casts(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'color' => 'string',
            'description' => 'string',
            'created_by' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function users()
    {
        return $this->morphedByMany(User::class, 'resource', 'labels_resources', 'label', 'resource_id');
    }

    public function permissions()
    {
        return $this->morphedByMany(Permission::class, 'resource', 'labels_resources', 'label', 'resource_id');
    }

    public function groups()
    {
        return $this->morphedByMany(Group::class, 'resource', 'labels_resources', 'label', 'resource_id');
    }

    public function wordlists()
    {
        return $this->morphedByMany(Wordlist::class, 'resource', 'labels_resources', 'label', 'resource_id');
    }

    public function engines()
    {
        return $this->morphedByMany(Engine::class, 'resource', 'labels_resources', 'label', 'resource_id');
    }

    public function targets()
    {
        return $this->morphedByMany(Target::class, 'resource', 'labels_resources', 'label', 'resource_id');
    }

    public function actions()
    {
        return $this->morphedByMany(Action::class, 'resource', 'labels_resources', 'label', 'resource_id');
    }
}
