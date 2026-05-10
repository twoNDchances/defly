<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupRequest;
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

    private function groupData(GroupRequest $request): array
    {
        return $this->onlyFields($request->validated(), [
            'name',
            'description',
        ]);
    }
}
