<?php

namespace App\Models;

use App\Enums\Phase;
use App\Enums\Principle\ValidationStatus;
use App\Observers\PrincipleObserver;
use App\Traits\Models\Labellable;
use App\Traits\Models\Owner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'level', 'phase', 'validation_status', 'validation_details', 'is_applied', 'description', 'created_by', 'is_locked'])]
#[ObservedBy(PrincipleObserver::class)]
class Principle extends Model
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
            'is_applied' => 'boolean',
            'description' => 'string',
            'created_by' => 'string',
            'is_locked' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function rules()
    {
        return $this->belongsToMany(Rule::class, 'principles_rules', 'principle', 'rule')
            ->withPivot('order')
            ->orderByPivot('order');
    }

    public function defenders()
    {
        return $this->belongsToMany(Defender::class, 'defenders_principles', 'principle', 'defender')
            ->withPivot('order')
            ->orderByPivot('order');
    }
}
