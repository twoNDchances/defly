<?php

namespace Database\Seeders;

use App\Enums\Datatype;
use App\Enums\Engine\Type;
use App\Models\Engine;
use App\Models\Label;
use Illuminate\Database\Seeder;

class EngineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Engine::query()->exists()) {
            return;
        }

        $label = Label::where('name', config('customization.backend.default_label'))->first();

        $engines = [
            [
                'name' => 'number-to-string',
                'input_datatype' => Datatype::Number,
                'type' => Type::ToString->value,
                'configurations' => null,
                'output_datatype' => Datatype::String,
                'description' => "Eng: Convert number input to string output.\nVie: Chuyển input kiểu số thành output kiểu chuỗi.",
            ],
            [
                'name' => 'string-lower',
                'input_datatype' => Datatype::String,
                'type' => Type::Lower->value,
                'configurations' => null,
                'output_datatype' => Datatype::String,
                'description' => "Eng: Convert text to lowercase.\nVie: Chuyển văn bản thành chữ thường.",
            ],
            [
                'name' => 'string-upper',
                'input_datatype' => Datatype::String,
                'type' => Type::Upper->value,
                'configurations' => null,
                'output_datatype' => Datatype::String,
                'description' => "Eng: Convert text to uppercase.\nVie: Chuyển văn bản thành chữ hoa.",
            ],
            [
                'name' => 'string-capitalize',
                'input_datatype' => Datatype::String,
                'type' => Type::Capitalize->value,
                'configurations' => null,
                'output_datatype' => Datatype::String,
                'description' => "Eng: Capitalize text.\nVie: Viết hoa ký tự đầu của văn bản.",
            ],
            [
                'name' => 'string-trim',
                'input_datatype' => Datatype::String,
                'type' => Type::Trim->value,
                'configurations' => null,
                'output_datatype' => Datatype::String,
                'description' => "Eng: Trim whitespace on both sides.\nVie: Cắt khoảng trắng ở đầu và cuối.",
            ],
            [
                'name' => 'string-trim-left',
                'input_datatype' => Datatype::String,
                'type' => Type::TrimLeft->value,
                'configurations' => null,
                'output_datatype' => Datatype::String,
                'description' => "Eng: Trim whitespace on the left side.\nVie: Cắt khoảng trắng bên trái.",
            ],
            [
                'name' => 'string-trim-right',
                'input_datatype' => Datatype::String,
                'type' => Type::TrimRight->value,
                'configurations' => null,
                'output_datatype' => Datatype::String,
                'description' => "Eng: Trim whitespace on the right side.\nVie: Cắt khoảng trắng bên phải.",
            ],
            [
                'name' => 'string-remove-whitespace',
                'input_datatype' => Datatype::String,
                'type' => Type::RemoveWhitespace->value,
                'configurations' => null,
                'output_datatype' => Datatype::String,
                'description' => "Eng: Remove all whitespace in text.\nVie: Loại bỏ toàn bộ khoảng trắng trong văn bản.",
            ],
            [
                'name' => 'string-length',
                'input_datatype' => Datatype::String,
                'type' => Type::Length->value,
                'configurations' => null,
                'output_datatype' => Datatype::Number,
                'description' => "Eng: Count text length.\nVie: Đếm độ dài văn bản.",
            ],
        ];

        foreach ($engines as $engine) {
            $record = Engine::firstOrCreate(['name' => $engine['name']], $engine);

            if ($label) {
                $record->labels()->sync($label->id);
            }
        }
    }
}
