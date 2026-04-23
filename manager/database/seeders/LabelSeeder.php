<?php

namespace Database\Seeders;

use App\Models\Label;
use App\Models\User;
use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Label::where('name', config('customization.backend.default_label'))->exists()) {
            return;
        }

        $email = config('customization.backend.default_credentials.user_email');
        $user = User::where('email', $email)->first();
        $label = Label::firstOrCreate(
            ['name' => config('customization.backend.default_label')],
            [
                'color' => '#a855f7',
                'description' => 'Default resources when the application first booted',
                'created_by' => $user->id,
            ],
        );
        $label->users()->sync($user->id);
    }
}
