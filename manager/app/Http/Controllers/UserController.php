<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function verify($email, $token)
    {
        $user = User::where('email', $email)->where('verification_token', $token)->first();
        if (!$user)
        {
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
}
