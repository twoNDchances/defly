<?php

namespace App\Traits\Models;

use App\Models\Timeline;
use App\Models\User;

trait Owner
{
    public function createdBy()
    {
        return $this->belongsTo(User::class, $this->getCreatedByField());
    }

    public function timelines()
    {
        return $this->morphMany(Timeline::class, 'resource');
    }

    protected function getCreatedByField()
    {
        return 'created_by';
    }
}
