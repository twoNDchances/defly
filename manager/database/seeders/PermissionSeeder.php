<?php

namespace Database\Seeders;

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
        $email = config('customization.backend.default_credentials.user_email');
        $user = User::where('email', $email)->first();
        $permissions = Security::generatePermissionList();
        foreach ($permissions as $permission) {
            $permission['created_by'] = $user->id;
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }
    }
}
