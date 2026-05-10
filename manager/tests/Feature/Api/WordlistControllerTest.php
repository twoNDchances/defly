<?php

namespace Tests\Feature\Api;

use App\Enums\Wordlist\Type as WordlistType;
use App\Models\Wordlist;
use Illuminate\Support\Facades\Storage;

class WordlistControllerTest extends ApiTestCase
{
    public function test_wordlists_payload_endpoint_is_accessible(): void
    {
        $this->apiJson('GET', $this->apiRoute('wordlists', 'payload'))->assertOk();
    }

    public function test_wordlists_api_supports_json_and_file_types_and_put_patch_behavior(): void
    {
        $this->apiJson('GET', $this->apiRoute('wordlists', 'index'))->assertOk();

        $jsonStore = $this->apiJson('POST', $this->apiRoute('wordlists', 'store'), [], [
            'name' => 'json-wordlist',
            'type' => WordlistType::Json->value,
            'word_json' => [
                ['word' => 'admin'],
                ['word' => 'debug'],
            ],
            'description' => 'JSON wordlist',
        ])->assertCreated();

        $jsonWordlistId = (string) $jsonStore->json('id');
        $this->apiJson('GET', $this->apiRoute('wordlists', 'show'), ['wordlist' => $jsonWordlistId])
            ->assertOk()
            ->assertJsonPath('id', $jsonWordlistId);

        $this->apiJson('POST', $this->apiRoute('wordlists', 'store'), [], [
            'name' => 'invalid-file-wordlist',
            'type' => WordlistType::File->value,
            'description' => 'Missing file',
        ])->assertUnprocessable()->assertJsonValidationErrors(['word_file']);

        $file = $this->fakeTextFile('words.txt', "alpha\nbeta\ngamma\n");
        $fileStore = $this->apiForm('POST', $this->apiRoute('wordlists', 'store'), [], [
            'name' => 'file-wordlist',
            'type' => WordlistType::File->value,
            'word_file' => $file,
            'description' => 'File wordlist',
        ])->assertCreated();

        $fileWordlistId = (string) $fileStore->json('id');
        $fileWordlist = Wordlist::query()->findOrFail($fileWordlistId);
        $originalPath = (string) $fileWordlist->word_file;

        $this->assertNotNull($fileWordlist->word_file);
        $this->assertTrue(Storage::disk('local')->exists($originalPath));

        $this->apiJson('PATCH', $this->apiRoute('wordlists', 'update'), ['wordlist' => $fileWordlistId], [
            'description' => 'Patched file wordlist',
        ])->assertOk()->assertJsonPath('description', 'Patched file wordlist');

        $this->apiJson('PUT', $this->apiRoute('wordlists', 'update'), ['wordlist' => $fileWordlistId], [
            'description' => 'Only description',
        ])->assertUnprocessable();

        $replacementFile = $this->fakeTextFile('replacement.txt', "one\ntwo\n");
        $this->apiForm('PUT', $this->apiRoute('wordlists', 'update'), ['wordlist' => $fileWordlistId], [
            'name' => 'file-wordlist-replaced',
            'type' => WordlistType::File->value,
            'word_file' => $replacementFile,
            'description' => 'Replaced file wordlist',
        ])->assertOk()->assertJsonPath('name', 'file-wordlist-replaced');

        $updatedFileWordlist = Wordlist::query()->findOrFail($fileWordlistId);
        $this->assertNotSame($originalPath, (string) $updatedFileWordlist->word_file);
        $this->assertFalse(Storage::disk('local')->exists($originalPath));
        $this->assertTrue(Storage::disk('local')->exists((string) $updatedFileWordlist->word_file));

        $this->apiJson('DELETE', $this->apiRoute('wordlists', 'destroy'), ['wordlist' => $jsonWordlistId])
            ->assertNoContent();
        $this->apiJson('DELETE', $this->apiRoute('wordlists', 'destroy'), ['wordlist' => $fileWordlistId])
            ->assertNoContent();

        $this->assertDatabaseMissing('wordlists', ['id' => $jsonWordlistId]);
        $this->assertDatabaseMissing('wordlists', ['id' => $fileWordlistId]);
        $this->assertFalse(Storage::disk('local')->exists((string) $updatedFileWordlist->word_file));
    }
}
