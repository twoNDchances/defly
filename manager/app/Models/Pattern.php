<?php

namespace App\Models;

use App\Enums\Datatype;
use App\Enums\Type;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'phase', 'type', 'datatype', 'description'])]
class Pattern extends Model
{
    use HasUuids;

    protected function casts()
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'phase' => 'integer',
            'type' => Type::class,
            'datatype' => Datatype::class,
            'description' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function targets()
    {
        return $this->hasMany(Target::class, 'pattern');
    }
}
