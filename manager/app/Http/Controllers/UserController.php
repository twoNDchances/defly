<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Requests\UserRelationRequest;
use App\Models\User;
use App\Services\ApiPayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UserController extends Controller
{
    public function index(UserRequest $request): JsonResponse
    {
        $currentUser = $request->user();
        $perPage = max(1, min((int) $request->integer('per_page', 15), 100));

        $users = User::query()
            ->when($currentUser, fn ($query) => $query->whereKeyNot($currentUser->getKey()))
            ->when(! $currentUser?->is_root, fn ($query) => $query->where('is_root', false))
            ->latest()
            ->paginate($perPage);

        return response()->json($users);
    }

    public function store(UserRequest $request): JsonResponse
    {
        $user = User::query()->create($this->userData($request));

        return response()->json($user, SymfonyResponse::HTTP_CREATED);
    }

    public function payload(Request $request): JsonResponse
    {
        $store = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '1234',
            'is_activated' => true,
            'is_verified' => true,
        ];

        $update = [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'password' => '1234',
            'is_activated' => true,
        ];

        if ($request->user()?->is_root) {
            $store['is_root'] = false;
            $update['is_root'] = false;
        }

        return response()->json(ApiPayload::resource('users', [
            'store' => [
                'method' => 'POST',
                'body' => $store,
            ],
            'update' => [
                'method' => 'PATCH',
                'path' => '{user}',
                'body' => $update,
            ],
            'list_permissions' => [
                'method' => 'GET',
                'path' => '{user}/permissions',
            ],
            'attach_permissions' => [
                'method' => 'POST',
                'path' => '{user}/permissions',
                'body' => [
                    'ids' => [
                        '<permission-id-1>',
                        '<permission-id-2>',
                    ],
                ],
            ],
            'detach_permissions' => [
                'method' => 'DELETE',
                'path' => '{user}/permissions',
                'body' => [
                    'ids' => [
                        '<permission-id-1>',
                    ],
                ],
            ],
            'list_groups' => [
                'method' => 'GET',
                'path' => '{user}/groups',
            ],
            'attach_groups' => [
                'method' => 'POST',
                'path' => '{user}/groups',
                'body' => [
                    'ids' => [
                        '<group-id-1>',
                        '<group-id-2>',
                    ],
                ],
            ],
            'detach_groups' => [
                'method' => 'DELETE',
                'path' => '{user}/groups',
                'body' => [
                    'ids' => [
                        '<group-id-1>',
                    ],
                ],
            ],
            'list_labels' => [
                'method' => 'GET',
                'path' => '{user}/labels',
            ],
            'attach_labels' => [
                'method' => 'POST',
                'path' => '{user}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                        '<label-id-2>',
                    ],
                ],
            ],
            'detach_labels' => [
                'method' => 'DELETE',
                'path' => '{user}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                    ],
                ],
            ],
        ]));
    }

    public function show(UserRequest $request, User $user): JsonResponse
    {
        return response()->json($user);
    }

    public function update(UserRequest $request, User $user): JsonResponse
    {
        $user->update($this->userData($request));

        return response()->json($user->refresh());
    }

    public function destroy(UserRequest $request, User $user): HttpResponse
    {
        $user->delete();

        return response()->noContent();
    }

    public function permissions(UserRelationRequest $request, User $user): JsonResponse
    {
        return response()->json($user->permissions()
            ->latest()
            ->get());
    }

    public function attachPermissions(UserRelationRequest $request, User $user): JsonResponse
    {
        $user->permissions()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($user->permissions()
            ->latest()
            ->get());
    }

    public function detachPermissions(UserRelationRequest $request, User $user): JsonResponse
    {
        $user->permissions()->detach($request->validated('ids', []));

        return response()->json($user->permissions()
            ->latest()
            ->get());
    }

    public function groups(UserRelationRequest $request, User $user): JsonResponse
    {
        return response()->json($user->groups()
            ->latest()
            ->get());
    }

    public function attachGroups(UserRelationRequest $request, User $user): JsonResponse
    {
        $user->groups()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($user->groups()
            ->latest()
            ->get());
    }

    public function detachGroups(UserRelationRequest $request, User $user): JsonResponse
    {
        $user->groups()->detach($request->validated('ids', []));

        return response()->json($user->groups()
            ->latest()
            ->get());
    }

    public function labels(UserRelationRequest $request, User $user): JsonResponse
    {
        return response()->json($user->labels()
            ->latest()
            ->get());
    }

    public function attachLabels(UserRelationRequest $request, User $user): JsonResponse
    {
        $user->labels()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($user->labels()
            ->latest()
            ->get());
    }

    public function detachLabels(UserRelationRequest $request, User $user): JsonResponse
    {
        $user->labels()->detach($request->validated('ids', []));

        return response()->json($user->labels()
            ->latest()
            ->get());
    }

    public function verify($email, $token)
    {
        $user = User::where('email', $email)->where('verification_token', $token)->first();
        if (! $user) {
            abort(404);
        }
        $user->markEmailAsVerified();
        $user->update([
            'verification_token' => null,
            'is_verified' => true,
        ]);
        Auth::login($user, true);

        return response()->redirectTo(route('filament.defly-manager.pages.dashboard'));
    }

    private function userData(UserRequest $request): array
    {
        $data = $request->validated();

        if (array_key_exists('password', $data) && blank($data['password'])) {
            unset($data['password']);
        }

        if (! $request->isMethod('post')) {
            unset($data['is_verified']);
        }

        if (! $request->user()?->is_root) {
            unset($data['is_root']);
        }

        return $data;
    }
}
