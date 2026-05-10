<?php

namespace App\Http\Controllers;

use App\Enums\Wordlist\Type;
use App\Http\Requests\WordlistRelationRequest;
use App\Http\Requests\WordlistRequest;
use App\Models\Wordlist;
use App\Services\ApiPayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class WordlistController extends Controller
{
    public function index(WordlistRequest $request): JsonResponse
    {
        $wordlists = Wordlist::query()
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($wordlists);
    }

    public function store(WordlistRequest $request): JsonResponse
    {
        $wordlist = Wordlist::query()->create($this->wordlistData($request));

        return response()->json($wordlist, SymfonyResponse::HTTP_CREATED);
    }

    public function payload(): JsonResponse
    {
        return response()->json(ApiPayload::resource('wordlists', [
            'store_file' => [
                'method' => 'POST',
                'body' => [
                    'name' => 'blocked-words-file',
                    'type' => Type::File->value,
                    'word_file' => '<uploaded-file: blocked-words.txt>',
                    'description' => 'File wordlist API example.',
                ],
            ],
            'store_json' => [
                'method' => 'POST',
                'body' => [
                    'name' => 'blocked-words-json',
                    'type' => Type::Json->value,
                    'word_json' => [
                        ['word' => 'admin'],
                        ['word' => 'debug'],
                    ],
                    'description' => 'JSON wordlist API example.',
                ],
            ],
            'update' => [
                'method' => 'PATCH',
                'path' => '{wordlist}',
                'body' => [
                    'description' => 'Updated wordlist description.',
                ],
            ],
            'list_labels' => [
                'method' => 'GET',
                'path' => '{wordlist}/labels',
            ],
            'attach_labels' => [
                'method' => 'POST',
                'path' => '{wordlist}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                        '<label-id-2>',
                    ],
                ],
            ],
            'detach_labels' => [
                'method' => 'DELETE',
                'path' => '{wordlist}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                    ],
                ],
            ],
        ]));
    }

    public function show(WordlistRequest $request, Wordlist $wordlist): JsonResponse
    {
        return response()->json($wordlist);
    }

    public function update(WordlistRequest $request, Wordlist $wordlist): JsonResponse
    {
        $wordlist->update($this->wordlistData($request));

        return response()->json($wordlist->refresh());
    }

    public function destroy(WordlistRequest $request, Wordlist $wordlist): HttpResponse
    {
        $wordlist->delete();

        return response()->noContent();
    }

    public function labels(WordlistRelationRequest $request, Wordlist $wordlist): JsonResponse
    {
        return response()->json($wordlist->labels()
            ->latest()
            ->get());
    }

    public function attachLabels(WordlistRelationRequest $request, Wordlist $wordlist): JsonResponse
    {
        $wordlist->labels()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($wordlist->labels()
            ->latest()
            ->get());
    }

    public function detachLabels(WordlistRelationRequest $request, Wordlist $wordlist): JsonResponse
    {
        $wordlist->labels()->detach($request->validated('ids', []));

        return response()->json($wordlist->labels()
            ->latest()
            ->get());
    }

    private function wordlistData(WordlistRequest $request): array
    {
        $data = $this->onlyFields($request->validated(), [
            'name',
            'type',
            'word_file',
            'word_json',
            'description',
        ]);

        if ($request->hasFile('word_file')) {
            $data['word_file'] = $request->file('word_file')->store('wordlists');
        }

        return $data;
    }
}
