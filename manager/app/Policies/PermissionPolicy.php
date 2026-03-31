<?php

namespace App\Policies;

use App\Models\Permission;
use App\Traits\Policies\Basic;

class PermissionPolicy
{
    use Basic;

    public function getModel()
    {
        return Permission::class;
    }
}
