<?php

namespace Tests\Support;

use App\Models\Wordlist;
use RuntimeException;

class ThrowingWordlist extends Wordlist
{
    public function getAttribute($key)
    {
        if ($key === 'word_json') {
            throw new RuntimeException('json failed');
        }

        return parent::getAttribute($key);
    }
}
