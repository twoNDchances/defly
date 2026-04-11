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

#[Fillable(['name', 'phase', 'type', 'datatype', 'description', 'pattern', 'wordlist', 'created_by'])]
#[ObservedBy(TargetObserver::class)]
class Target extends Model
{
    use HasUuids, Labellable, Owner;

    protected function casts()
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'phase' => Phase::class,
            'type' => Type::class,
            'datatype' => Datatype::class,
            'description' => 'string',
            'pattern' => 'string',
            'wordlist' => 'string',
            'created_by' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function pattern()
    {
        return $this->belongsTo(Pattern::class, 'pattern');
    }

    public function wordlist()
    {
        return $this->belongsTo(Wordlist::class, 'wordlist');
    }
}
