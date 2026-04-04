<?php

namespace App\Traits\Models;

use App\Models\Label;

trait Labellable
{
    public function labels()
    {
        return $this->morphToMany(Label::class, 'resource', 'labels_resources', 'resource_id', 'label');
    }
}
