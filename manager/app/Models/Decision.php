<?php

namespace App\Models;

use App\Enums\Decision\Action;
use App\Enums\Decision\Condition;
use App\Enums\Decision\Direction;
use App\Observers\DecisionObserver;
use App\Traits\Models\Labellable;
use App\Traits\Models\Owner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'direction', 'condition', 'score', 'action', 'configurations', 'description', 'created_by', 'is_locked'])]
#[ObservedBy(DecisionObserver::class)]
class Decision extends Model
{
    use HasUuids, Labellable, Owner;

    protected function casts()
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'direction' => Direction::class,
            'condition' => Condition::class,
            'score' => 'float',
            'action' => Action::class,
            'configurations' => 'array',
            'description' => 'string',
            'created_by' => 'string',
            'is_locked' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function defenders()
    {
        return $this->belongsToMany(Defender::class, 'defenders_decisions', 'decision', 'defender')
            ->withPivot('order', 'is_implemented')
            ->orderByPivot('order');
    }
}
