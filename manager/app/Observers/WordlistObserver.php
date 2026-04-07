<?php

namespace App\Observers;

use App\Enums\Wordlist\Type;
use App\Models\Wordlist;
use App\Traits\Observers\After;
use App\Traits\Observers\Before;
use Illuminate\Support\Facades\Storage;

class WordlistObserver
{
    use After, Before;

    public function saving(Wordlist $wordlist): void
    {
        $count = 0;
        switch ($wordlist->type) {
            case Type::File:
                if ($wordlist->word_file && Storage::exists($wordlist->word_file)) {
                    $content = Storage::get($wordlist->word_file);
                    $count = count(array_filter(explode("\n", $content), fn ($line) => filled(trim($line))));
                    $wordlist->word_json = null;
                }
                break;
            case Type::Json:
                if ($wordlist->word_json) {
                    $count = count($wordlist->word_json);
                    $wordlist->word_file = null;
                }
                break;
        }
        $wordlist->word_count = $count;
    }

    public function updating(Wordlist $wordlist): void
    {
        if ($wordlist->isDirty('word_file')) {
            $oldFile = $wordlist->getOriginal('word_file');
            if ($oldFile && $oldFile !== $wordlist->word_file && Storage::exists($oldFile)) {
                Storage::delete($oldFile);
            }
        }
    }

    public function deleting(Wordlist $wordlist): void
    {
        if ($wordlist->word_file && Storage::exists($wordlist->word_file)) {
            Storage::delete($wordlist->word_file);
        }
    }
}
