<?php

namespace App\Traits\Observers;

use App\Services\Logger;
use Illuminate\Database\Eloquent\Model;

trait After
{
    /**
     * Handle the Model "saved" event.
     */
    public function saved(Model $model): void
    {
        //
    }

    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        Logger::created($model);
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        Logger::updated($model);
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        Logger::deleted($model);
    }
}
