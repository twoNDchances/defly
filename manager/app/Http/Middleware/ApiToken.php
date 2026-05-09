<?php

namespace App\Http\Middleware;

use App\Models\Key;
use App\Models\User;
use App\Services\ApiAuthentication;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('head')) {
            return response()->json([
                'message' => __('apis.messages.failed.method_not_allowed', ['method' => 'HEAD']),
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $user = $this->authenticateBasic($request);

        if (! $user) {
            return $this->unauthorized(__('apis.messages.failed.basic_authentication'), true);
        }

        $token = $this->getToken($request);

        if (blank($token)) {
            return $this->unauthorized(__('apis.messages.failed.missing_token'));
        }

        $key = $this->findValidKey($user, $token);

        if (! $key) {
            return $this->unauthorized(__('apis.messages.failed.token'));
        }

        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);
        $request->attributes->set('authenticated_key', $key);

        return $next($request);
    }

    private function authenticateBasic(Request $request): ?User
    {
        $email = $request->getUser();
        $password = $request->getPassword();

        if (blank($email) || blank($password)) {
            return null;
        }

        $user = User::query()
            ->where('email', $email)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    private function getToken(Request $request): mixed
    {
        return ApiAuthentication::tokenFrom($request);
    }

    private function findValidKey(User $user, mixed $token): ?Key
    {
        $token = (string) $token;

        return Key::query()
            ->where('created_by', $user->id)
            ->where(function ($query) {
                $query->whereNull('expired_at')
                    ->orWhere('expired_at', '>', now());
            })
            ->get()
            ->first(fn (Key $key): bool => Hash::check($token, $key->token));
    }

    private function unauthorized(string $message, bool $basic = false): Response
    {
        $headers = $basic ? ['WWW-Authenticate' => 'Basic realm="Defly Manager API"'] : [];

        return response()->json([
            'message' => $message,
        ], Response::HTTP_UNAUTHORIZED, $headers);
    }
}
