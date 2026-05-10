<?php

namespace App\Http\Controllers;

use App\Http\Requests\LabelRelationRequest;
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

    public function users(LabelRelationRequest $request, Label $label): JsonResponse
    {
        return response()->json($label->users()
            ->latest()
            ->get());
    }

    public function attachUsers(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->users()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($label->users()
            ->latest()
            ->get());
    }

    public function detachUsers(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->users()->detach($request->validated('ids', []));

        return response()->json($label->users()
            ->latest()
            ->get());
    }

    public function permissions(LabelRelationRequest $request, Label $label): JsonResponse
    {
        return response()->json($label->permissions()
            ->latest()
            ->get());
    }

    public function attachPermissions(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->permissions()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($label->permissions()
            ->latest()
            ->get());
    }

    public function detachPermissions(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->permissions()->detach($request->validated('ids', []));

        return response()->json($label->permissions()
            ->latest()
            ->get());
    }

    public function groups(LabelRelationRequest $request, Label $label): JsonResponse
    {
        return response()->json($label->groups()
            ->latest()
            ->get());
    }

    public function attachGroups(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->groups()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($label->groups()
            ->latest()
            ->get());
    }

    public function detachGroups(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->groups()->detach($request->validated('ids', []));

        return response()->json($label->groups()
            ->latest()
            ->get());
    }

    public function wordlists(LabelRelationRequest $request, Label $label): JsonResponse
    {
        return response()->json($label->wordlists()
            ->latest()
            ->get());
    }

    public function attachWordlists(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->wordlists()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($label->wordlists()
            ->latest()
            ->get());
    }

    public function detachWordlists(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->wordlists()->detach($request->validated('ids', []));

        return response()->json($label->wordlists()
            ->latest()
            ->get());
    }

    public function engines(LabelRelationRequest $request, Label $label): JsonResponse
    {
        return response()->json($label->engines()
            ->latest()
            ->get());
    }

    public function attachEngines(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->engines()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($label->engines()
            ->latest()
            ->get());
    }

    public function detachEngines(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->engines()->detach($request->validated('ids', []));

        return response()->json($label->engines()
            ->latest()
            ->get());
    }

    public function targets(LabelRelationRequest $request, Label $label): JsonResponse
    {
        return response()->json($label->targets()
            ->latest()
            ->get());
    }

    public function attachTargets(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->targets()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($label->targets()
            ->latest()
            ->get());
    }

    public function detachTargets(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->targets()->detach($request->validated('ids', []));

        return response()->json($label->targets()
            ->latest()
            ->get());
    }

    public function actions(LabelRelationRequest $request, Label $label): JsonResponse
    {
        return response()->json($label->actions()
            ->latest()
            ->get());
    }

    public function attachActions(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->actions()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($label->actions()
            ->latest()
            ->get());
    }

    public function detachActions(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->actions()->detach($request->validated('ids', []));

        return response()->json($label->actions()
            ->latest()
            ->get());
    }

    public function rules(LabelRelationRequest $request, Label $label): JsonResponse
    {
        return response()->json($label->rules()
            ->latest()
            ->get());
    }

    public function attachRules(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->rules()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($label->rules()
            ->latest()
            ->get());
    }

    public function detachRules(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->rules()->detach($request->validated('ids', []));

        return response()->json($label->rules()
            ->latest()
            ->get());
    }

    public function principles(LabelRelationRequest $request, Label $label): JsonResponse
    {
        return response()->json($label->principles()
            ->latest()
            ->get());
    }

    public function attachPrinciples(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->principles()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($label->principles()
            ->latest()
            ->get());
    }

    public function detachPrinciples(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->principles()->detach($request->validated('ids', []));

        return response()->json($label->principles()
            ->latest()
            ->get());
    }

    public function decisions(LabelRelationRequest $request, Label $label): JsonResponse
    {
        return response()->json($label->decisions()
            ->latest()
            ->get());
    }

    public function attachDecisions(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->decisions()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($label->decisions()
            ->latest()
            ->get());
    }

    public function detachDecisions(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->decisions()->detach($request->validated('ids', []));

        return response()->json($label->decisions()
            ->latest()
            ->get());
    }

    public function defenders(LabelRelationRequest $request, Label $label): JsonResponse
    {
        return response()->json($label->defenders()
            ->latest()
            ->get());
    }

    public function attachDefenders(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->defenders()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($label->defenders()
            ->latest()
            ->get());
    }

    public function detachDefenders(LabelRelationRequest $request, Label $label): JsonResponse
    {
        $label->defenders()->detach($request->validated('ids', []));

        return response()->json($label->defenders()
            ->latest()
            ->get());
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
