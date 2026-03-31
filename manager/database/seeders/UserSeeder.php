<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configurationPath = 'customization.backend.default_credentials';

        $name = config("$configurationPath.user_name");
        $email = config("$configurationPath.user_email");
        $password = config("$configurationPath.user_password");

        if (User::where('email', $email)->first()) {
            return;
        }

        if ($password == 'random') {
            $filePath = base_path('credentials.txt');
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $plainPassword = Str::random(16);
            file_put_contents(
                $filePath,
                "Email: $email\nPassword: $plainPassword\n",
                FILE_APPEND,
            );
        }

        $user = User::create([
            'email' => $email,
            'name' => $name,
            'is_root' => true,
            'is_verified' => true,
            'password' => Hash::make($plainPassword),
        ]);
        $user->markEmailAsVerified();
        $user->update([
            'created_by' => $user->id,
        ]);
    }
}
