<?php

namespace Database\Seeders;

use App\Enums\Action\Type;
use App\Models\Action;
use App\Models\Label;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Action::query()->exists()) {
            return;
        }

        $label = Label::where('name', config('customization.backend.default_label'))->first();
        $email = config('customization.backend.default_credentials.user_email');
        $user = User::where('email', $email)->first();

        $actions = [
            [
                'name' => 'allow',
                'type' => Type::Allow,
                'configurations' => null,
                'description' => "Eng: Stop other actions and allow request/response to continue.\nVie: Dừng các hành động tiếp theo và cho phép request/response tiếp tục.",
            ],
            [
                'name' => 'report',
                'type' => Type::Report,
                'configurations' => null,
                'description' => "Eng: Mark this event for reporting without changing the flow.\nVie: Đánh dấu sự kiện để báo cáo mà không thay đổi luồng xử lý.",
            ],
        ];

        foreach ($actions as $action) {
            $action['created_by'] = $user->id;

            $record = Action::firstOrCreate(['name' => $action['name']], $action);

            if ($label) {
                $record->labels()->sync($label->id);
            }
        }
    }
}
