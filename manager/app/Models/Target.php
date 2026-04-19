<?php

namespace App\Models;

use App\Enums\Datatype;
use App\Enums\Phase;
use App\Enums\Type;
use App\Observers\TargetObserver;
use App\Traits\Models\Labellable;
use App\Traits\Models\Owner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'phase', 'type', 'datatype', 'description', 'pattern_id', 'wordlist_id', 'created_by', 'locked'])]
#[ObservedBy(TargetObserver::class)]
class Target extends Model
{
    use HasUuids, Labellable, Owner;

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'phase' => Phase::class,
            'type' => Type::class,
            'datatype' => Datatype::class,
            'description' => 'string',
            'pattern_id' => 'string',
            'wordlist_id' => 'string',
            'created_by' => 'string',
            'locked' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function pattern()
    {
        return $this->belongsTo(Pattern::class, 'pattern_id');
    }

    public function wordlist()
    {
        return $this->belongsTo(Wordlist::class, 'wordlist_id');
    }

    public function engines()
    {
        return $this->belongsToMany(Engine::class, 'targets_engines', 'target', 'engine')
            ->withPivot('order')
            ->orderByPivot('order');
    }
}
