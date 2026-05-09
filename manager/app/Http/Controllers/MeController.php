<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeRequest;
use App\Services\ApiPayload;
use Illuminate\Http\JsonResponse;

class MeController extends Controller
{
    public function show(MeRequest $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function update(MeRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $user->update(array_intersect_key($data, array_flip([
            'name',
            'email',
            'password',
        ])));

        return response()->json($user->refresh());
    }

    public function payload(): JsonResponse
    {
        return response()->json(ApiPayload::resource('me', [
            'replace_profile' => [
                'method' => 'PUT',
                'body' => [
                    'name' => 'Updated Name',
                    'email' => 'updated@example.com',
                    'current_password' => '<current-password>',
                ],
            ],
            'update_name' => [
                'method' => 'PATCH',
                'body' => [
                    'name' => 'Updated Name',
                ],
            ],
            'update_email' => [
                'method' => 'PATCH',
                'body' => [
                    'email' => 'updated@example.com',
                    'current_password' => '<current-password>',
                ],
            ],
            'update_password' => [
                'method' => 'PATCH',
                'body' => [
                    'current_password' => '<current-password>',
                    'password' => '1234',
                    'password_confirmation' => '1234',
                ],
            ],
        ]));
    }
}
