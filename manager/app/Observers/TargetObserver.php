<?php

namespace App\Observers;

use App\Enums\Datatype;
use App\Enums\Type;
use App\Models\Pattern;
use App\Models\Target;
use App\Services\Lock;
use App\Traits\Observers\After;
use App\Traits\Observers\Before;

class TargetObserver
{
    use After, Before;

    public function saving(Target $target): void
    {
        if ($target->type == Type::Getter) {
            $target->pattern_id = null;
        }
        if ($target->pattern_id) {
            $target->datatype = Pattern::find($target->pattern_id)?->datatype;
        }
        if ($target->datatype != Datatype::Array || $target->pattern_id) {
            $target->wordlist_id = null;
        }
    }

    public function saved(Target $target): void
    {
        Lock::syncByForeignKey($target);
    }

    public function deleting(Target $target): void
    {
        Lock::syncByDeleting($target);
    }
}
