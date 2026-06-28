<?php

namespace App\Models;

use App\Observers\ConservationObserver;
use App\Traits\Models\Owner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'is_pinned', 'created_by'])]
#[ObservedBy(ConservationObserver::class)]
class Conservation extends Model
{
    use HasUuids, Owner;

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'title' => 'string',
            'is_pinned' => 'boolean',
            'created_by' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('id');
    }
}
