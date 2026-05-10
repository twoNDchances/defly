<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
use App\Http\Requests\PermissionRelationRequest;
use App\Models\Permission;
use App\Services\ApiPayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PermissionController extends Controller
{
    public function index(PermissionRequest $request): JsonResponse
    {
        $permissions = Permission::query()
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($permissions);
    }

    public function store(PermissionRequest $request): JsonResponse
    {
        $permission = Permission::query()->create($this->permissionData($request));

        return response()->json($permission, SymfonyResponse::HTTP_CREATED);
    }

    public function payload(): JsonResponse
    {
        return response()->json(ApiPayload::resource('permissions', [
            'store' => [
                'method' => 'POST',
                'body' => [
                    'name' => 'User:List',
                    'applied_for' => 'User',
                    'action' => 'viewAny',
                    'description' => 'Permission API example.',
                ],
            ],
            'update' => [
                'method' => 'PATCH',
                'path' => '{permission}',
                'body' => [
                    'description' => 'Updated permission description.',
                ],
            ],
            'list_users' => [
                'method' => 'GET',
                'path' => '{permission}/users',
            ],
            'attach_users' => [
                'method' => 'POST',
                'path' => '{permission}/users',
                'body' => [
                    'ids' => [
                        '<user-id-1>',
                        '<user-id-2>',
                    ],
                ],
            ],
            'detach_users' => [
                'method' => 'DELETE',
                'path' => '{permission}/users',
                'body' => [
                    'ids' => [
                        '<user-id-1>',
                    ],
                ],
            ],
            'list_groups' => [
                'method' => 'GET',
                'path' => '{permission}/groups',
            ],
            'attach_groups' => [
                'method' => 'POST',
                'path' => '{permission}/groups',
                'body' => [
                    'ids' => [
                        '<group-id-1>',
                        '<group-id-2>',
                    ],
                ],
            ],
            'detach_groups' => [
                'method' => 'DELETE',
                'path' => '{permission}/groups',
                'body' => [
                    'ids' => [
                        '<group-id-1>',
                    ],
                ],
            ],
            'list_labels' => [
                'method' => 'GET',
                'path' => '{permission}/labels',
            ],
            'attach_labels' => [
                'method' => 'POST',
                'path' => '{permission}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                        '<label-id-2>',
                    ],
                ],
            ],
            'detach_labels' => [
                'method' => 'DELETE',
                'path' => '{permission}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                    ],
                ],
            ],
        ]));
    }

    public function show(PermissionRequest $request, Permission $permission): JsonResponse
    {
        return response()->json($permission);
    }

    public function update(PermissionRequest $request, Permission $permission): JsonResponse
    {
        $permission->update($this->permissionData($request));

        return response()->json($permission->refresh());
    }

    public function destroy(PermissionRequest $request, Permission $permission): HttpResponse
    {
        $permission->delete();

        return response()->noContent();
    }

    public function users(PermissionRelationRequest $request, Permission $permission): JsonResponse
    {
        return response()->json($permission->users()
            ->latest()
            ->get());
    }

    public function attachUsers(PermissionRelationRequest $request, Permission $permission): JsonResponse
    {
        $permission->users()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($permission->users()
            ->latest()
            ->get());
    }

    public function detachUsers(PermissionRelationRequest $request, Permission $permission): JsonResponse
    {
        $permission->users()->detach($request->validated('ids', []));

        return response()->json($permission->users()
            ->latest()
            ->get());
    }

    public function groups(PermissionRelationRequest $request, Permission $permission): JsonResponse
    {
        return response()->json($permission->groups()
            ->latest()
            ->get());
    }

    public function attachGroups(PermissionRelationRequest $request, Permission $permission): JsonResponse
    {
        $permission->groups()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($permission->groups()
            ->latest()
            ->get());
    }

    public function detachGroups(PermissionRelationRequest $request, Permission $permission): JsonResponse
    {
        $permission->groups()->detach($request->validated('ids', []));

        return response()->json($permission->groups()
            ->latest()
            ->get());
    }

    public function labels(PermissionRelationRequest $request, Permission $permission): JsonResponse
    {
        return response()->json($permission->labels()
            ->latest()
            ->get());
    }

    public function attachLabels(PermissionRelationRequest $request, Permission $permission): JsonResponse
    {
        $permission->labels()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($permission->labels()
            ->latest()
            ->get());
    }

    public function detachLabels(PermissionRelationRequest $request, Permission $permission): JsonResponse
    {
        $permission->labels()->detach($request->validated('ids', []));

        return response()->json($permission->labels()
            ->latest()
            ->get());
    }

    private function permissionData(PermissionRequest $request): array
    {
        return $this->onlyFields($request->validated(), [
            'name',
            'description',
            'applied_for',
            'action',
        ]);
    }
}
