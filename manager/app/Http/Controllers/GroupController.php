<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupRequest;
use App\Http\Requests\GroupRelationRequest;
use App\Models\Group;
use App\Services\ApiPayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class GroupController extends Controller
{
    public function index(GroupRequest $request): JsonResponse
    {
        $groups = Group::query()
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($groups);
    }

    public function store(GroupRequest $request): JsonResponse
    {
        $group = Group::query()->create($this->groupData($request));

        return response()->json($group, SymfonyResponse::HTTP_CREATED);
    }

    public function payload(): JsonResponse
    {
        return response()->json(ApiPayload::resource('groups', [
            'store' => [
                'method' => 'POST',
                'body' => [
                    'name' => 'security-team',
                    'description' => 'Group API example.',
                ],
            ],
            'update' => [
                'method' => 'PATCH',
                'path' => '{group}',
                'body' => [
                    'description' => 'Updated group description.',
                ],
            ],
            'list_users' => [
                'method' => 'GET',
                'path' => '{group}/users',
            ],
            'attach_users' => [
                'method' => 'POST',
                'path' => '{group}/users',
                'body' => [
                    'ids' => [
                        '<user-id-1>',
                        '<user-id-2>',
                    ],
                ],
            ],
            'detach_users' => [
                'method' => 'DELETE',
                'path' => '{group}/users',
                'body' => [
                    'ids' => [
                        '<user-id-1>',
                    ],
                ],
            ],
            'list_permissions' => [
                'method' => 'GET',
                'path' => '{group}/permissions',
            ],
            'attach_permissions' => [
                'method' => 'POST',
                'path' => '{group}/permissions',
                'body' => [
                    'ids' => [
                        '<permission-id-1>',
                        '<permission-id-2>',
                    ],
                ],
            ],
            'detach_permissions' => [
                'method' => 'DELETE',
                'path' => '{group}/permissions',
                'body' => [
                    'ids' => [
                        '<permission-id-1>',
                    ],
                ],
            ],
            'list_labels' => [
                'method' => 'GET',
                'path' => '{group}/labels',
            ],
            'attach_labels' => [
                'method' => 'POST',
                'path' => '{group}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                        '<label-id-2>',
                    ],
                ],
            ],
            'detach_labels' => [
                'method' => 'DELETE',
                'path' => '{group}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                    ],
                ],
            ],
        ]));
    }

    public function show(GroupRequest $request, Group $group): JsonResponse
    {
        return response()->json($group);
    }

    public function update(GroupRequest $request, Group $group): JsonResponse
    {
        $group->update($this->groupData($request));

        return response()->json($group->refresh());
    }

    public function destroy(GroupRequest $request, Group $group): HttpResponse
    {
        $group->delete();

        return response()->noContent();
    }

    public function users(GroupRelationRequest $request, Group $group): JsonResponse
    {
        return response()->json($group->users()
            ->latest()
            ->get());
    }

    public function attachUsers(GroupRelationRequest $request, Group $group): JsonResponse
    {
        $group->users()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($group->users()
            ->latest()
            ->get());
    }

    public function detachUsers(GroupRelationRequest $request, Group $group): JsonResponse
    {
        $group->users()->detach($request->validated('ids', []));

        return response()->json($group->users()
            ->latest()
            ->get());
    }

    public function permissions(GroupRelationRequest $request, Group $group): JsonResponse
    {
        return response()->json($group->permissions()
            ->latest()
            ->get());
    }

    public function attachPermissions(GroupRelationRequest $request, Group $group): JsonResponse
    {
        $group->permissions()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($group->permissions()
            ->latest()
            ->get());
    }

    public function detachPermissions(GroupRelationRequest $request, Group $group): JsonResponse
    {
        $group->permissions()->detach($request->validated('ids', []));

        return response()->json($group->permissions()
            ->latest()
            ->get());
    }

    public function labels(GroupRelationRequest $request, Group $group): JsonResponse
    {
        return response()->json($group->labels()
            ->latest()
            ->get());
    }

    public function attachLabels(GroupRelationRequest $request, Group $group): JsonResponse
    {
        $group->labels()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($group->labels()
            ->latest()
            ->get());
    }

    public function detachLabels(GroupRelationRequest $request, Group $group): JsonResponse
    {
        $group->labels()->detach($request->validated('ids', []));

        return response()->json($group->labels()
            ->latest()
            ->get());
    }

    private function groupData(GroupRequest $request): array
    {
        return $this->onlyFields($request->validated(), [
            'name',
            'description',
        ]);
    }
}
