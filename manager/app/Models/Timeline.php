<?php

namespace App\Models;

use App\Observers\TimelineObserver;
use App\Traits\Models\Owner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['created_by', 'ipv4', 'ipv6', 'method', 'path', 'action', 'resource_type', 'resource_id'])]
#[ObservedBy(TimelineObserver::class)]
class Timeline extends Model
{
    use HasUuids, Owner;

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'created_by' => 'string',
            'ipv4' => 'string',
            'ipv6' => 'string',
            'method' => 'string',
            'path' => 'string',
            'action' => 'string',
            'resource_type' => 'string',
            'resource_id' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function resource()
    {
        return $this->morphTo();
    }
}
