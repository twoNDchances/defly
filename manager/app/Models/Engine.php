<?php

namespace App\Models;

use App\Enums\Datatype;
use App\Observers\EngineObserver;
use App\Traits\Models\Labellable;
use App\Traits\Models\Owner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'input_datatype', 'type', 'configurations', 'output_datatype', 'description', 'created_by', 'locked'])]
#[ObservedBy(EngineObserver::class)]
class Engine extends Model
{
    use HasUuids, Labellable, Owner;

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'input_datatype' => Datatype::class,
            'type' => 'string',
            'configurations' => 'array',
            'output_datatype' => Datatype::class,
            'description' => 'string',
            'created_by' => 'string',
            'locked' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function targets()
    {
        return $this->belongsToMany(Target::class, 'targets_engines', 'engine', 'target')
            ->withPivot('order')
            ->orderByPivot('order');
    }
}
