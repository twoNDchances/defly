<?php

namespace App\Models;

use App\Observers\GuardObserver;
use App\Traits\Models\Labellable;
use App\Traits\Models\Owner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'description', 'expired_at', 'created_by'])]
#[ObservedBy(GuardObserver::class)]
class Guard extends Model
{
    use HasUuids, Labellable, Owner;

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'description' => 'string',
            'expired_at' => 'datetime',
            'created_by' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function defenders()
    {
        return $this->belongsToMany(Defender::class, 'guards_defenders', 'guard', 'defender');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'guards_users', 'guard', 'user');
    }
}
