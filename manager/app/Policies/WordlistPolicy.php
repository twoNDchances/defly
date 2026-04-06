<?php

namespace App\Policies;

use App\Models\Wordlist;
use App\Traits\Policies\Basic;

class WordlistPolicy
{
    use Basic;

    public function getModel()
    {
        return Wordlist::class;
    }
}
