<?php

namespace App\Policies;

use App\Models\Permission;
use App\Traits\Policies\Basic;
use App\Traits\Policies\Relationship;

class PermissionPolicy
{
    use Basic;
    use Relationship;

    public function getModel()
    {
        return Permission::class;
    }
}
