<?php

namespace Database\Seeders;

use App\Models\Label;
use App\Models\Permission;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = Permission::get()->all();
        $allPermissionIds = [];
        foreach ($permissions as $permission) {
            if ($permission->action == 'all') {
                $allPermissionIds[] = $permission->id;
            }
        }
        $email = config('customization.backend.default_credentials.user_email');
        $user = User::where(['email' => $email])->first();
        $group = Group::firstOrCreate(
            ['name' => 'manager'],
            ['created_by' => $user->id],
        );
        $group->permissions()->sync($allPermissionIds);
        $label = Label::where('name', config('customization.backend.default_label'))->first();
        $label->groups()->sync($group->id);
    }
}
