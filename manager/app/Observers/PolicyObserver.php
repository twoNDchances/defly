<?php

namespace App\Observers;

use App\Models\Policy;
use App\Services\Lock;
use App\Traits\Observers\After;
use App\Traits\Observers\Before;

class PolicyObserver
{
    use After, Before;

    public function deleting(Policy $policy): void
    {
        Lock::syncByDeleting($policy);
    }
}
