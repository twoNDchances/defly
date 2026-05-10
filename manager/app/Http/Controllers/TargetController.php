<?php

namespace App\Http\Controllers;

use App\Enums\Datatype;
use App\Enums\Phase;
use App\Enums\Type;
use App\Http\Requests\TargetRequest;
use App\Http\Requests\TargetRelationRequest;
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
            'list_engines' => [
                'method' => 'GET',
                'path' => '{target}/engines',
            ],
            'attach_engines' => [
                'method' => 'POST',
                'path' => '{target}/engines',
                'body' => [
                    'ids' => [
                        '<engine-id-1>',
                        '<engine-id-2>',
                    ],
                ],
            ],
            'detach_engines' => [
                'method' => 'DELETE',
                'path' => '{target}/engines',
                'body' => [
                    'ids' => [
                        '<engine-id-1>',
                    ],
                ],
            ],
            'list_labels' => [
                'method' => 'GET',
                'path' => '{target}/labels',
            ],
            'attach_labels' => [
                'method' => 'POST',
                'path' => '{target}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                        '<label-id-2>',
                    ],
                ],
            ],
            'detach_labels' => [
                'method' => 'DELETE',
                'path' => '{target}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                    ],
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

    public function engines(TargetRelationRequest $request, Target $target): JsonResponse
    {
        return response()->json($target->engines()
            ->latest()
            ->get());
    }

    public function attachEngines(TargetRelationRequest $request, Target $target): JsonResponse
    {
        $ids = $request->validated('ids', []);
        $relation = $target->engines();
        $relation->syncWithoutDetaching($ids);
        $this->syncRelationLocks($relation->getRelated()::class, $ids);

        return response()->json($target->engines()
            ->latest()
            ->get());
    }

    public function detachEngines(TargetRelationRequest $request, Target $target): JsonResponse
    {
        $ids = $request->validated('ids', []);
        $relation = $target->engines();
        $relation->detach($ids);
        $this->syncRelationLocks($relation->getRelated()::class, $ids);

        return response()->json($target->engines()
            ->latest()
            ->get());
    }

    public function labels(TargetRelationRequest $request, Target $target): JsonResponse
    {
        return response()->json($target->labels()
            ->latest()
            ->get());
    }

    public function attachLabels(TargetRelationRequest $request, Target $target): JsonResponse
    {
        $target->labels()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($target->labels()
            ->latest()
            ->get());
    }

    public function detachLabels(TargetRelationRequest $request, Target $target): JsonResponse
    {
        $target->labels()->detach($request->validated('ids', []));

        return response()->json($target->labels()
            ->latest()
            ->get());
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
