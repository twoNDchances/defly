<?php

namespace App\Observers;

use App\Enums\Rule\Comparator;
use App\Models\Rule;
use App\Models\Target;
use App\Models\Wordlist;
use App\Services\ForeignKeyLock;
use App\Traits\Observers\After;
use App\Traits\Observers\Before;

class RuleObserver
{
    use After, Before;

    public function saving(Rule $rule): void
    {
        if (! in_array($rule->comparator, [Comparator::Similar, Comparator::Search, Comparator::Check, Comparator::CheckRegExp])) {
            $rule->wordlist_id = null;
        }
    }

    public function saved(Rule $rule): void
    {
        ForeignKeyLock::syncForForeignKeys($rule, [
            'target_id' => Target::class,
            'wordlist_id' => Wordlist::class,
        ]);

        ForeignKeyLock::syncModel($rule);
    }
}
