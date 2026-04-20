<?php

namespace App\Observers;

use App\Models\Action;
use App\Services\ForeignKeyLock;
use App\Traits\Observers\After;
use App\Traits\Observers\Before;

class ActionObserver
{
    use After, Before;

    public function saved(Action $action): void
    {
        ForeignKeyLock::syncModel($action);
    }
}
