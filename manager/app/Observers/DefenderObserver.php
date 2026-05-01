<?php

namespace App\Observers;

use App\Models\Defender;
use App\Services\Lock;
use App\Traits\Observers\After;
use App\Traits\Observers\Before;

class DefenderObserver
{
    use After, Before;

    public function deleting(Defender $defender): void
    {
        Lock::syncByDeleting($defender);
    }
}
