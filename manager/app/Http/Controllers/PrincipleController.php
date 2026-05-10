<?php

namespace App\Http\Controllers;

use App\Enums\Phase;
use App\Http\Requests\PrincipleRequest;
use App\Models\Principle;
use App\Services\ApiPayload;
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
