<?php

namespace App\Models;

use App\Enums\Wordlist\WordType;
use App\Observers\WordlistObserver;
use App\Traits\Models\Labellable;
use App\Traits\Models\Owner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'word_type', 'word_file', 'word_json', 'word_count', 'description', 'created_by'])]
#[ObservedBy(WordlistObserver::class)]
class Wordlist extends Model
{
    use HasUuids, Labellable, Owner;

    protected function casts()
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'word_type' => WordType::class,
            'word_file' => 'string',
            'word_json' => 'array',
            'word_count' => 'integer',
            'description' => 'string',
            'created_by' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
