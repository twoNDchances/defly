<?php

namespace App\Observers;

use App\Mail\VerificationMail;
use App\Models\User;
use App\Services\Identification;
use App\Traits\Observers\After;
use App\Traits\Observers\Before;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserObserver
{
    use After;
    use Before;

    public function creating(User $user): void
    {
        $user->created_by = Identification::getId();
        if (! $user->is_verified) {
            $user->verification_token = Str::uuid();
        }
        if ($user->password) {
            $user->password = Hash::make($user->password);
        }
    }

    public function created(User $user): void
    {
        if (! $user->is_verified) {
            try {
                Mail::to($user->email)->queue(new VerificationMail($user->name, $user->email, $user->verification_token));
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
            }
        } else {
            $user->markEmailAsVerified();
            $user->save();
        }
    }
}
