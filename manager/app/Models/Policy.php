<?php

namespace App\Models;

use App\Enums\Phase;
use App\Enums\Policy\ValidationStatus;
use App\Observers\PolicyObserver;
use App\Traits\Models\Labellable;
use App\Traits\Models\Owner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'level', 'phase', 'validation_status', 'validation_details', 'description', 'created_by', 'is_locked'])]
#[ObservedBy(PolicyObserver::class)]
class Policy extends Model
{
    use HasUuids, Labellable, Owner;

    protected function casts()
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'level' => 'integer',
            'phase' => Phase::class,
            'validation_status' => ValidationStatus::class,
            'validation_details' => 'array',
            'description' => 'string',
            'created_by' => 'string',
            'is_locked' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function rules()
    {
        return $this->belongsToMany(Rule::class, 'policies_rules', 'policy', 'rule')
            ->withPivot('order')
            ->orderByPivot('order');
    }
}
