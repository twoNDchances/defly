<?php

namespace App\Traits\Observers;

use App\Services\Identification;
use Illuminate\Database\Eloquent\Model;

trait Before
{
    /**
     * Handle the Model "creating" event.
     */
    public function creating(Model $model): void
    {
        $model->created_by = Identification::getId();
    }

    /**
     * Handle the Model "updating" event.
     */
    public function updating(Model $model): void
    {
        //
    }

    /**
     * Handle the Model "deleting" event.
     */
    public function deleting(Model $model): void
    {
        //
    }
}
