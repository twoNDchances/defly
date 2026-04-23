<?php

namespace Database\Seeders;

use App\Enums\Type;
use App\Models\Label;
use App\Models\Pattern;
use App\Models\Target;
use Illuminate\Database\Seeder;

class TargetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Target::query()->exists()) {
            return;
        }

        $label = Label::where('name', config('customization.backend.default_label'))->first();

        $patterns = Pattern::query()
            ->whereIn('type', [Type::Full->value, Type::Meta->value])
            ->get();

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
                ],
            );

            if ($label) {
                $record->labels()->sync($label->id);
            }
        }
    }
}
