<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardRelationRequest;
use App\Http\Requests\GuardRequest;
use App\Models\Guard;
use App\Services\ApiPayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class GuardController extends Controller
{
    public function index(GuardRequest $request): JsonResponse
    {
        $guards = Guard::query()
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($guards);
    }

    public function store(GuardRequest $request): JsonResponse
    {
        $guard = Guard::query()->create($this->guardData($request));

        return response()->json($guard, SymfonyResponse::HTTP_CREATED);
    }

    public function payload(): JsonResponse
    {
        return response()->json(ApiPayload::resource('guards', [
            'store' => [
                'method' => 'POST',
                'body' => [
                    'name' => 'production-edge-operators',
                    'description' => 'Guard API example.',
                    'expired_at' => null,
                ],
            ],
            'update' => [
                'method' => 'PATCH',
                'path' => '{guard}',
                'body' => [
                    'description' => 'Updated guard description.',
                    'expired_at' => null,
                ],
            ],
            'list_users' => [
                'method' => 'GET',
                'path' => '{guard}/users',
            ],
            'attach_users' => [
                'method' => 'POST',
                'path' => '{guard}/users',
                'body' => [
                    'ids' => [
                        '<user-id-1>',
                        '<user-id-2>',
                    ],
                ],
            ],
            'detach_users' => [
                'method' => 'DELETE',
                'path' => '{guard}/users',
                'body' => [
                    'ids' => [
                        '<user-id-1>',
                    ],
                ],
            ],
            'list_defenders' => [
                'method' => 'GET',
                'path' => '{guard}/defenders',
            ],
            'attach_defenders' => [
                'method' => 'POST',
                'path' => '{guard}/defenders',
                'body' => [
                    'ids' => [
                        '<defender-id-1>',
                        '<defender-id-2>',
                    ],
                ],
            ],
            'detach_defenders' => [
                'method' => 'DELETE',
                'path' => '{guard}/defenders',
                'body' => [
                    'ids' => [
                        '<defender-id-1>',
                    ],
                ],
            ],
            'list_labels' => [
                'method' => 'GET',
                'path' => '{guard}/labels',
            ],
            'attach_labels' => [
                'method' => 'POST',
                'path' => '{guard}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                        '<label-id-2>',
                    ],
                ],
            ],
            'detach_labels' => [
                'method' => 'DELETE',
                'path' => '{guard}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                    ],
                ],
            ],
        ]));
    }

    public function show(GuardRequest $request, Guard $guard): JsonResponse
    {
        return response()->json($guard);
    }

    public function update(GuardRequest $request, Guard $guard): JsonResponse
    {
        $guard->update($this->guardData($request));

        return response()->json($guard->refresh());
    }

    public function destroy(GuardRequest $request, Guard $guard): HttpResponse
    {
        $guard->delete();

        return response()->noContent();
    }

    public function users(GuardRelationRequest $request, Guard $guard): JsonResponse
    {
        return response()->json($guard->users()
            ->latest()
            ->get());
    }

    public function attachUsers(GuardRelationRequest $request, Guard $guard): JsonResponse
    {
        $guard->users()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($guard->users()
            ->latest()
            ->get());
    }

    public function detachUsers(GuardRelationRequest $request, Guard $guard): JsonResponse
    {
        $guard->users()->detach($request->validated('ids', []));

        return response()->json($guard->users()
            ->latest()
            ->get());
    }

    public function defenders(GuardRelationRequest $request, Guard $guard): JsonResponse
    {
        return response()->json($guard->defenders()
            ->visibleTo($request->user())
            ->latest()
            ->get());
    }

    public function attachDefenders(GuardRelationRequest $request, Guard $guard): JsonResponse
    {
        $guard->defenders()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($guard->defenders()
            ->visibleTo($request->user())
            ->latest()
            ->get());
    }

    public function detachDefenders(GuardRelationRequest $request, Guard $guard): JsonResponse
    {
        $guard->defenders()->detach($request->validated('ids', []));

        return response()->json($guard->defenders()
            ->visibleTo($request->user())
            ->latest()
            ->get());
    }

    public function labels(GuardRelationRequest $request, Guard $guard): JsonResponse
    {
        return response()->json($guard->labels()
            ->latest()
            ->get());
    }

    public function attachLabels(GuardRelationRequest $request, Guard $guard): JsonResponse
    {
        $guard->labels()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($guard->labels()
            ->latest()
            ->get());
    }

    public function detachLabels(GuardRelationRequest $request, Guard $guard): JsonResponse
    {
        $guard->labels()->detach($request->validated('ids', []));

        return response()->json($guard->labels()
            ->latest()
            ->get());
    }

    private function guardData(GuardRequest $request): array
    {
        return $this->onlyFields($request->validated(), [
            'name',
            'description',
            'expired_at',
        ]);
    }
}
