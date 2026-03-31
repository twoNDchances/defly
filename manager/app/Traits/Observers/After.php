<?php

namespace App\Traits\Observers;

use Illuminate\Database\Eloquent\Model;

trait After
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        //
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        //
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        //
    }
}
