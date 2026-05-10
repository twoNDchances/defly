<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
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
