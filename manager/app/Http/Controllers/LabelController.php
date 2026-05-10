<?php

namespace App\Http\Controllers;

use App\Http\Requests\LabelRequest;
use App\Models\Label;
use App\Services\ApiPayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class LabelController extends Controller
{
    public function index(LabelRequest $request): JsonResponse
    {
        $labels = Label::query()
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($labels);
    }

    public function store(LabelRequest $request): JsonResponse
    {
        $label = Label::query()->create($this->labelData($request));

        return response()->json($label, SymfonyResponse::HTTP_CREATED);
    }

    public function payload(): JsonResponse
    {
        return response()->json(ApiPayload::resource('labels', [
            'store' => [
                'method' => 'POST',
                'body' => [
                    'name' => 'security',
                    'color' => '#ff5500',
                    'description' => 'Label API example.',
                ],
            ],
            'update' => [
                'method' => 'PATCH',
                'path' => '{label}',
                'body' => [
                    'color' => '#0ea5e9',
                ],
            ],
        ]));
    }

    public function show(LabelRequest $request, Label $label): JsonResponse
    {
        return response()->json($label);
    }

    public function update(LabelRequest $request, Label $label): JsonResponse
    {
        $label->update($this->labelData($request));

        return response()->json($label->refresh());
    }

    public function destroy(LabelRequest $request, Label $label): HttpResponse
    {
        $label->delete();

        return response()->noContent();
    }

    private function labelData(LabelRequest $request): array
    {
        return $this->onlyFields($request->validated(), [
            'name',
            'color',
            'description',
        ]);
    }
}
