<?php

namespace Tests\Feature\Observers;

use App\Enums\Wordlist\Type as WordlistType;
use App\Mail\VerificationMail;
use App\Models\Label;
use App\Models\User;
use App\Models\Wordlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnershipAndWordlistObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_observers_set_ownership_verification_word_counts_and_cleanup(): void
    {
        Mail::fake();
        $owner = User::factory()->create([
            'is_root' => true,
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $this->actingAs($owner);

        $label = Label::query()->create([
            'name' => 'owned-label-'.Str::lower(Str::random(6)),
            'color' => '#111111',
        ]);
        $this->assertSame($owner->id, $label->created_by);

        $verifiedUser = User::factory()->create([
            'is_verified' => true,
            'is_activated' => true,
        ]);
        $this->assertNotNull($verifiedUser->fresh()->email_verified_at);

        User::factory()->create([
            'email' => 'verify-me@example.com',
            'is_verified' => false,
            'is_activated' => true,
        ]);
        Mail::assertQueued(VerificationMail::class);

        Storage::put('wordlists/source.txt', "alpha\n\nbeta\n");
        $fileWordlist = Wordlist::query()->create([
            'name' => 'file-wordlist-'.Str::lower(Str::random(6)),
            'type' => WordlistType::File->value,
            'word_file' => 'wordlists/source.txt',
            'word_json' => [['word' => 'ignored']],
        ]);
        $this->assertSame(2, $fileWordlist->fresh()->word_count);
        $this->assertNull($fileWordlist->fresh()->word_json);

        $fileWordlist->delete();
        Storage::assertMissing('wordlists/source.txt');
    }
}
