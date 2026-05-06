<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use App\Traits\Policies\Basic;

class ReportPolicy
{
    use Basic;

    public function getModel()
    {
        return Report::class;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Report $report): bool
    {
        return false;
    }
}
