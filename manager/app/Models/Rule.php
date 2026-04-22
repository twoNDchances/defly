<?php

namespace App\Models;

use App\Enums\Phase;
use App\Enums\Rule\Comparator;
use App\Observers\RuleObserver;
use App\Traits\Models\Labellable;
use App\Traits\Models\Owner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'phase', 'target_id', 'comparator', 'is_inversed', 'configurations', 'wordlist_id', 'description', 'created_by', 'is_locked'])]
#[ObservedBy(RuleObserver::class)]
class Rule extends Model
{
    use HasUuids, Labellable, Owner;

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'phase' => Phase::class,
            'target_id' => 'string',
            'comparator' => Comparator::class,
            'is_inversed' => 'boolean',
            'configurations' => 'array',
            'wordlist_id' => 'string',
            'description' => 'string',
            'created_by' => 'string',
            'is_locked' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function target()
    {
        return $this->belongsTo(Target::class, 'target_id');
    }

    public function wordlist()
    {
        return $this->belongsTo(Wordlist::class, 'wordlist_id');
    }

    public function actions()
    {
        return $this->belongsToMany(Action::class, 'rules_actions', 'rule', 'action')
            ->withPivot('order')
            ->orderByPivot('order');
    }

    public function policies()
    {
        return $this->belongsToMany(Policy::class, 'policies_rules', 'rule', 'policy')
            ->withPivot('order')
            ->orderByPivot('order');
    }
}
