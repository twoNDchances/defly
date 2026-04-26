<?php

namespace App\Policies;

use App\Models\Wordlist;
use App\Traits\Policies\Basic;
use App\Traits\Policies\Extra;

class WordlistPolicy
{
    use Basic, Extra;

    public function getModel()
    {
        return Wordlist::class;
    }
}
