<?php

namespace App\Http\Controllers;

use App\Enums\Datatype;
use App\Enums\Phase;
use App\Enums\Type;
use App\Http\Requests\TargetRequest;
use App\Models\Target;
use App\Services\ApiPayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class TargetController extends Controller
{
    public function index(TargetRequest $request): JsonResponse
    {
        $targets = Target::query()
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($targets);
    }

    public function store(TargetRequest $request): JsonResponse
    {
        $target = Target::query()->create($this->targetData($request));

        return response()->json($target, SymfonyResponse::HTTP_CREATED);
    }

    public function payload(): JsonResponse
    {
        return response()->json(ApiPayload::resource('targets', [
            'store_string_getter' => [
                'method' => 'POST',
                'body' => [
                    'phase' => Phase::One->value,
                    'type' => Type::Getter->value,
                    'name' => 'request-ip',
                    'datatype' => Datatype::String->value,
                    'description' => 'Target API example.',
                ],
            ],
            'store_array_getter' => [
                'method' => 'POST',
                'body' => [
                    'phase' => Phase::One->value,
                    'type' => Type::Getter->value,
                    'name' => 'request-tags',
                    'datatype' => Datatype::Array->value,
                    'wordlist_id' => '<wordlist-id>',
                    'description' => 'Array target API example.',
                ],
            ],
            'store_full_pattern' => [
                'method' => 'POST',
                'body' => [
                    'phase' => Phase::One->value,
                    'type' => Type::Full->value,
                    'pattern_id' => '<pattern-id>',
                    'name' => 'request-full',
                    'datatype' => Datatype::String->value,
                    'description' => 'Pattern-backed target API example.',
                ],
            ],
            'update' => [
                'method' => 'PATCH',
                'path' => '{target}',
                'body' => [
                    'description' => 'Updated target description.',
                ],
            ],
        ]));
    }

    public function show(TargetRequest $request, Target $target): JsonResponse
    {
        return response()->json($target);
    }

    public function update(TargetRequest $request, Target $target): JsonResponse
    {
        $target->update($this->targetData($request));

        return response()->json($target->refresh());
    }

    public function destroy(TargetRequest $request, Target $target): HttpResponse
    {
        $target->delete();

        return response()->noContent();
    }

    private function targetData(TargetRequest $request): array
    {
        return $this->onlyFields($request->validated(), [
            'name',
            'phase',
            'type',
            'datatype',
            'description',
            'pattern_id',
            'wordlist_id',
        ]);
    }
}
