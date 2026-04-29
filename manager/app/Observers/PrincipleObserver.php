<?php

namespace App\Observers;

use App\Models\Principle;
use App\Services\Lock;
use App\Traits\Observers\After;
use App\Traits\Observers\Before;

class PrincipleObserver
{
    use After, Before;

    public function deleting(Principle $principle): void
    {
        Lock::syncByDeleting($principle);
    }
}
