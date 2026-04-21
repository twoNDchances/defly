<?php

namespace App\Models;

use App\Enums\Action\Type;
use App\Observers\ActionObserver;
use App\Traits\Models\Labellable;
use App\Traits\Models\Owner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'type', 'configurations', 'description', 'created_by', 'is_locked'])]
#[ObservedBy(ActionObserver::class)]
class Action extends Model
{
    use HasUuids, Labellable, Owner;

    protected function casts()
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'type' => Type::class,
            'configurations' => 'array',
            'description' => 'string',
            'created_by' => 'string',
            'is_locked' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function rules()
    {
        return $this->belongsToMany(Rule::class, 'rules_actions', 'action', 'rule')
            ->withPivot('order')
            ->orderByPivot('order');
    }
}
