<?php

namespace Database\Seeders;

use App\Models\Label;
use App\Models\Permission;
use App\Models\User;
use App\Services\Security;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Permission::query()->exists()) {
            return;
        }

        $email = config('customization.backend.default_credentials.user_email');
        $user = User::where('email', $email)->first();
        $permissionList = Security::generatePermissionList();
        $permissionIds = [];
        foreach ($permissionList as $permissionData) {
            $permissionData['created_by'] = $user->id;
            $permission = Permission::firstOrCreate(['name' => $permissionData['name']], $permissionData);
            $permissionIds[] = $permission->id;
        }
        $label = Label::where('name', config('customization.backend.default_label'))->first();
        $label->permissions()->sync($permissionIds);
    }
}
