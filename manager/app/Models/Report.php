<?php

namespace App\Models;

use App\Observers\ReportObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['metas', 'request_headers', 'request_body', 'response_headers', 'response_body', 'rule_details', 'triggered_by', 'created_by', 'is_reviewed'])]
#[ObservedBy(ReportObserver::class)]
class Report extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'metas' => 'array',
            'request_headers' => 'array',
            'request_body' => 'array',
            'response_headers' => 'array',
            'response_body' => 'array',
            'rule_details' => 'array',
            'triggered_by' => 'string',
            'created_by' => 'string',
            'is_reviewed' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function triggeredBy()
    {
        return $this->belongsTo(Action::class, 'triggered_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(Defender::class, 'created_by');
    }
}
