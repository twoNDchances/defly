<?php

namespace Database\Seeders;

use App\Models\Label;
use App\Models\Pattern;
use App\Models\Target;
use App\Models\User;
use Illuminate\Database\Seeder;

class TargetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $label = Label::where('name', config('customization.backend.default_label'))->first();
        $email = config('customization.backend.default_credentials.user_email');
        $user = User::where('email', $email)->first();

        $patterns = Pattern::query()->get();

        foreach ($patterns as $pattern) {
            $record = Target::firstOrCreate(
                ['name' => $pattern->name],
                [
                    'phase' => $pattern->phase,
                    'type' => $pattern->type->value,
                    'datatype' => $pattern->datatype->value,
                    'description' => "Eng: Target created from pattern '{$pattern->name}'.\nVie: Target được tạo từ pattern '{$pattern->name}'.",
                    'pattern_id' => $pattern->id,
                    'wordlist_id' => null,
                    'created_by' => $user->id,
                ],
            );

            if ($label) {
                $record->labels()->sync($label->id);
            }
        }
    }
}
