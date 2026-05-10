<?php

namespace App\Http\Controllers;

use App\Enums\Phase;
use App\Enums\Principle\ValidationStatus;
use App\Http\Requests\PrincipleActionRequest;
use App\Http\Requests\PrincipleRequest;
use App\Http\Requests\PrincipleRelationRequest;
use App\Jobs\PrincipleValidation;
use App\Models\Principle;
use App\Services\ApiPayload;
use App\Services\Logger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PrincipleController extends Controller
{
    public function index(PrincipleRequest $request): JsonResponse
    {
        $principles = Principle::query()
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($principles);
    }

    public function store(PrincipleRequest $request): JsonResponse
    {
        $principle = Principle::query()->create($this->principleData($request));

        return response()->json($principle, SymfonyResponse::HTTP_CREATED);
    }

    public function payload(): JsonResponse
    {
        return response()->json(ApiPayload::resource('principles', [
            'store' => [
                'method' => 'POST',
                'body' => [
                    'name' => 'request-security',
                    'level' => 1,
                    'phase' => Phase::One->value,
                    'description' => 'Principle API example.',
                ],
            ],
            'update' => [
                'method' => 'PATCH',
                'path' => '{principle}',
                'body' => [
                    'description' => 'Updated principle description.',
                ],
            ],
            'list_rules' => [
                'method' => 'GET',
                'path' => '{principle}/rules',
            ],
            'attach_rules' => [
                'method' => 'POST',
                'path' => '{principle}/rules',
                'body' => [
                    'ids' => [
                        '<rule-id-1>',
                        '<rule-id-2>',
                    ],
                ],
            ],
            'detach_rules' => [
                'method' => 'DELETE',
                'path' => '{principle}/rules',
                'body' => [
                    'ids' => [
                        '<rule-id-1>',
                    ],
                ],
            ],
            'list_labels' => [
                'method' => 'GET',
                'path' => '{principle}/labels',
            ],
            'attach_labels' => [
                'method' => 'POST',
                'path' => '{principle}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                        '<label-id-2>',
                    ],
                ],
            ],
            'detach_labels' => [
                'method' => 'DELETE',
                'path' => '{principle}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                    ],
                ],
            ],
            'validate' => [
                'method' => 'POST',
                'path' => '{principle}/validate',
            ],
        ]));
    }

    public function show(PrincipleRequest $request, Principle $principle): JsonResponse
    {
        return response()->json($principle);
    }

    public function update(PrincipleRequest $request, Principle $principle): JsonResponse
    {
        $principle->update($this->principleData($request));

        return response()->json($principle->refresh());
    }

    public function destroy(PrincipleRequest $request, Principle $principle): HttpResponse
    {
        $principle->delete();

        return response()->noContent();
    }

    public function rules(PrincipleRelationRequest $request, Principle $principle): JsonResponse
    {
        $phase = $principle->getRawOriginal('phase');

        return response()->json($principle->rules()
            ->where('phase', $phase)
            ->latest()
            ->get());
    }

    public function attachRules(PrincipleRelationRequest $request, Principle $principle): JsonResponse
    {
        $ids = $request->validated('ids', []);
        $phase = $principle->getRawOriginal('phase');
        $relation = $principle->rules();
        $relation->syncWithoutDetaching($ids);
        $this->syncRelationLocks($relation->getRelated()::class, $ids);

        return response()->json($principle->rules()
            ->where('phase', $phase)
            ->latest()
            ->get());
    }

    public function detachRules(PrincipleRelationRequest $request, Principle $principle): JsonResponse
    {
        $ids = $request->validated('ids', []);
        $phase = $principle->getRawOriginal('phase');
        $relation = $principle->rules();
        $relation->detach($ids);
        $this->syncRelationLocks($relation->getRelated()::class, $ids);

        return response()->json($principle->rules()
            ->where('phase', $phase)
            ->latest()
            ->get());
    }

    public function labels(PrincipleRelationRequest $request, Principle $principle): JsonResponse
    {
        return response()->json($principle->labels()
            ->latest()
            ->get());
    }

    public function attachLabels(PrincipleRelationRequest $request, Principle $principle): JsonResponse
    {
        $principle->labels()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($principle->labels()
            ->latest()
            ->get());
    }

    public function detachLabels(PrincipleRelationRequest $request, Principle $principle): JsonResponse
    {
        $principle->labels()->detach($request->validated('ids', []));

        return response()->json($principle->labels()
            ->latest()
            ->get());
    }

    public function validate(PrincipleActionRequest $request, Principle $principle): JsonResponse
    {
        $principle->validation_status = ValidationStatus::Pending;
        $principle->save();

        PrincipleValidation::dispatch($principle->id);
        Logger::log($principle, 'validate');

        return response()->json($principle->refresh());
    }

    private function principleData(PrincipleRequest $request): array
    {
        return $this->onlyFields($request->validated(), [
            'name',
            'level',
            'phase',
            'description',
        ]);
    }
}
