<?php

namespace App\Traits\Models;

use App\Models\User;

trait Owner
{
    public function createdBy()
    {
        return $this->belongsTo(User::class, $this->getCreatedByField());
    }

    protected function getCreatedByField()
    {
        return 'created_by';
    }
}
