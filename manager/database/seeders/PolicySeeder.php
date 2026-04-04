<?php

namespace Database\Seeders;

use App\Models\Label;
use App\Models\Permission;
use App\Models\Policy;
use App\Models\User;
use Illuminate\Database\Seeder;

class PolicySeeder extends Seeder
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
        $policy = Policy::firstOrCreate(
            ['name' => 'manager'],
            ['created_by' => $user->id],
        );
        $policy->permissions()->sync($allPermissionIds);
        $label = Label::where('name', config('customization.backend.default_label'))->first();
        $label->policies()->sync($policy->id);
    }
}
