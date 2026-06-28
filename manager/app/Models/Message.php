<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['conservation_id', 'role', 'content', 'resources'])]
class Message extends Model
{
    use HasUuids;

    protected $touches = ['conservation'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'conservation_id' => 'string',
            'role' => 'string',
            'content' => 'string',
            'resources' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function conservation()
    {
        return $this->belongsTo(Conservation::class);
    }
}
