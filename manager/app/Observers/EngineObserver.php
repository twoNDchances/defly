<?php

namespace App\Observers;

use App\Models\Engine;
use App\Services\ForeignKeyLock;
use App\Traits\Observers\After;
use App\Traits\Observers\Before;

class EngineObserver
{
    use After, Before;

    public function saved(Engine $engine): void
    {
        ForeignKeyLock::syncModel($engine);
    }
}
